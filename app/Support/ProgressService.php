<?php

namespace App\Support;

use App\Models\Badge;
use App\Models\Challenge;
use App\Models\Ride;
use App\Models\Rider;
use Illuminate\Support\Carbon;

/**
 * Keeps a rider's challenge and badge progress in step with their verified rides.
 *
 * Challenges and badges with metric "manual" are left alone (the admin sets them).
 * Automatic ones are recalculated whenever a rider's rides change. Reaching a goal:
 *  - completes the challenge (and unlocks its reward badge, if one is set)
 *  - unlocks the badge
 * and sends the rider an alert. Badges are never locked again automatically.
 */
class ProgressService
{
    public const METRICS = [
        'manual' => 'Set by admin',
        'distance' => 'Verified distance (km)',
        'rides' => 'Number of verified rides',
        'ride_days' => 'Number of different days ridden',
        'morning_rides' => 'Rides started before 9:00 AM',
    ];

    public const MORNING_CUTOFF = '09:00:00';

    /** Recalculate every rider (e.g. after a challenge or badge rule changes). */
    public function recalculateAll(): void
    {
        Rider::query()->each(fn (Rider $rider) => $this->recalculate($rider));
    }

    public function recalculate(Rider $rider): void
    {
        $this->recalculateChallenges($rider);
        $this->recalculateBadges($rider);
    }

    /**
     * Value of a metric for a rider's rides, optionally limited to a date window.
     * Counts verified rides by default; pass Ride::STATUS_PENDING to see what is waiting for review.
     */
    public function metricValue(Rider $rider, string $metric, ?Carbon $from = null, ?Carbon $to = null, string $status = Ride::STATUS_VERIFIED): float
    {
        $rides = $rider->rides()
            ->where('status', $status)
            ->when($from, fn ($q) => $q->whereDate('ride_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('ride_date', '<=', $to));

        return match ($metric) {
            'distance' => (float) $rides->sum('distance_km'),
            'rides' => (float) $rides->count(),
            'ride_days' => (float) $rides->distinct()->count('ride_date'),
            'morning_rides' => (float) $rides->whereNotNull('ride_time')->where('ride_time', '<', self::MORNING_CUTOFF)->count(),
            default => 0.0,
        };
    }

    /** Start of the counting window for a joined challenge: its start date, or the day the rider joined. */
    public function challengeWindowStart(Challenge $challenge): Carbon
    {
        return $challenge->starts_at ?? Carbon::parse($challenge->pivot->created_at)->startOfDay();
    }

    /**
     * How much of a joined challenge is waiting for review (pending rides inside its dates).
     * Manual challenges have nothing pending.
     */
    public function pendingForChallenge(Rider $rider, Challenge $challenge): float
    {
        if ($challenge->metric === 'manual' || ! $challenge->pivot) {
            return 0.0;
        }

        return $this->metricValue($rider, $challenge->metric, $this->challengeWindowStart($challenge), $challenge->ends_at, Ride::STATUS_PENDING);
    }

    /**
     * Progress (0-100) towards a badge for any rider, including riders who have
     * never touched it yet: automatic badges are worked out from their rides.
     */
    public function badgePercent(Rider $rider, Badge $badge, $pivot = null): int
    {
        if ($pivot?->unlocked_at) {
            return 100;
        }

        if ($badge->is_auto) {
            return (int) min(100, floor($this->metricValue($rider, $badge->metric) / (float) $badge->target_value * 100));
        }

        return (int) ($pivot->progress_percent ?? 0);
    }

    /** Words for "… to go" on the Next Badge card: metric => [one, many]. */
    public const METRIC_UNITS = [
        'distance' => ['km', 'km'],
        'rides' => ['more ride', 'more rides'],
        'ride_days' => ['more day of riding', 'more days of riding'],
        'morning_rides' => ['more morning ride', 'more morning rides'],
    ];

    /**
     * The badge a rider can earn next by riding: the locked automatic badge they
     * are closest to (highest %, then the admin's badge order). Badges the admin
     * awards by hand are left out, because rides don't move them.
     *
     * Returns the badge with extra fields: percent, value, target, pending,
     * pending_percent and to_go (e.g. "3.5 km to go"). Null when every
     * ride-based badge is already unlocked.
     */
    public function nextBadge(Rider $rider): ?Badge
    {
        $unlocked = $rider->badges()->wherePivotNotNull('unlocked_at')->pluck('badges.id');

        return Badge::query()
            ->where('metric', '!=', 'manual')
            ->where('target_value', '>', 0)
            ->whereNotIn('id', $unlocked)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Badge $badge) use ($rider) {
                $target = (float) $badge->target_value;
                $value = $this->metricValue($rider, $badge->metric);
                $pending = $this->metricValue($rider, $badge->metric, status: Ride::STATUS_PENDING);

                $badge->value = $value;
                $badge->target = $target;
                $badge->percent = (int) min(100, floor($value / $target * 100));
                $badge->pending = $pending;
                $badge->pending_percent = (int) max(0, min(100 - $badge->percent, floor(($value + $pending) / $target * 100) - $badge->percent));

                $left = max(0, $target - $value);
                [$one, $many] = self::METRIC_UNITS[$badge->metric] ?? ['', ''];
                $badge->to_go = Format::number($left).' '.($left == 1 ? $one : $many).' to go';

                return $badge;
            })
            ->sortBy([['percent', 'desc'], ['sort_order', 'asc']])
            ->first();
    }

    private function recalculateChallenges(Rider $rider): void
    {
        foreach ($rider->challenges()->get() as $challenge) {
            if ($challenge->metric === 'manual') {
                // Manual challenges: only make sure completion follows the stored progress.
                $this->syncCompletion($rider, $challenge, (float) $challenge->pivot->progress_value);

                continue;
            }

            // Window: the challenge's dates; without a start date, count from when the rider joined.
            $from = $this->challengeWindowStart($challenge);
            $value = $this->metricValue($rider, $challenge->metric, $from, $challenge->ends_at);

            $rider->challenges()->updateExistingPivot($challenge->id, ['progress_value' => $value]);
            $this->syncCompletion($rider, $challenge, $value);
        }
    }

    private function syncCompletion(Rider $rider, Challenge $challenge, float $value): void
    {
        $reached = $challenge->target_value > 0 && $value >= $challenge->target_value;
        $wasCompleted = (bool) $challenge->pivot->completed_at;

        if ($reached && ! $wasCompleted) {
            $rider->challenges()->updateExistingPivot($challenge->id, ['completed_at' => now()]);

            $rider->notifications()->create([
                'title' => 'Challenge Complete!',
                'message' => "You completed \"{$challenge->title}\" and earned ".number_format($challenge->reward_points).' points!',
                'icon' => 'emoji_events',
                'color' => 'secondary',
            ]);

            if ($challenge->badge_id && ($badge = Badge::find($challenge->badge_id))) {
                $this->unlock($rider, $badge);
            }
        } elseif (! $reached && $wasCompleted && $challenge->metric !== 'manual') {
            // A verified ride was later rejected or removed: the challenge is open again.
            $rider->challenges()->updateExistingPivot($challenge->id, ['completed_at' => null]);
        }
    }

    private function recalculateBadges(Rider $rider): void
    {
        $owned = $rider->badges()->get()->keyBy('id');

        $auto = Badge::where('metric', '!=', 'manual')->where('target_value', '>', 0)->get();

        foreach ($auto as $badge) {
            $pivot = $owned->get($badge->id)?->pivot;
            if ($pivot?->unlocked_at) {
                continue;
            }

            $value = $this->metricValue($rider, $badge->metric);
            $percent = (int) min(100, floor($value / (float) $badge->target_value * 100));

            if ($percent >= 100) {
                $this->unlock($rider, $badge);
            } elseif ($pivot) {
                $rider->badges()->updateExistingPivot($badge->id, ['progress_percent' => $percent]);
            } elseif ($percent > 0) {
                $rider->badges()->attach($badge->id, ['progress_percent' => $percent]);
            }
        }
    }

    /** Unlocks a badge for a rider (once) and sends them an alert. */
    public function unlock(Rider $rider, Badge $badge): void
    {
        $existing = $rider->badges()->where('badges.id', $badge->id)->first();
        if ($existing?->pivot->unlocked_at) {
            return;
        }

        $values = ['unlocked_at' => now(), 'progress_percent' => 100];
        $existing
            ? $rider->badges()->updateExistingPivot($badge->id, $values)
            : $rider->badges()->attach($badge->id, $values);

        $rider->notifications()->create([
            'title' => 'New Badge Unlocked!',
            'message' => "You earned the \"{$badge->name}\" badge. {$badge->description}",
            'icon' => 'stars',
            'color' => 'primary',
        ]);
    }
}
