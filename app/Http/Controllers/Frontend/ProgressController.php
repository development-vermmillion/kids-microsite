<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Ride;
use App\Support\CurrentRider;
use App\Support\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function index(CurrentRider $current, ProgressService $service): View
    {
        $rider = $current->get();
        abort_unless($rider, 404);

        $verified = $rider->rides()->verified();
        $pending = $rider->rides()->where('status', Ride::STATUS_PENDING);

        // The rider's challenges that are still running, with verified + waiting progress.
        $running = Challenge::running()->pluck('id');
        $challenges = $rider->challenges()->get()
            ->filter(fn ($c) => $running->contains($c->id))
            ->sortBy('sort_order')
            ->map(function ($challenge) use ($rider, $service) {
                $target = (float) $challenge->target_value;
                $value = (float) $challenge->pivot->progress_value;
                $waiting = $challenge->pivot->completed_at ? 0.0 : $service->pendingForChallenge($rider, $challenge);

                $challenge->progress = $value;
                $challenge->pending = $waiting;
                $challenge->is_completed = (bool) $challenge->pivot->completed_at;
                $challenge->percent = $target > 0 ? (int) min(100, floor($value / $target * 100)) : 0;
                $challenge->pending_percent = $target > 0 ? (int) min(100 - $challenge->percent, ceil($waiting / $target * 100)) : 0;

                return $challenge;
            })
            ->values();

        return view('frontend.pages.progress', [
            'totalDistance' => (float) (clone $verified)->sum('distance_km'),
            'totalRides' => (clone $verified)->count(),
            'pendingDistance' => (float) (clone $pending)->sum('distance_km'),
            'pendingRides' => (clone $pending)->count(),
            'badgesEarned' => $rider->badges()->wherePivotNotNull('unlocked_at')->count(),
            'hoursInSaddle' => (int) round((clone $verified)->sum('duration_minutes') / 60),
            'recentRides' => $rider->rides()->orderByDesc('ride_date')->latest('id')->take(4)->get(),
            'totalUploaded' => $rider->rides()->count(),
            'challenges' => $challenges,
        ]);
    }

    /** "View All History": every ride the rider uploaded, with a status filter. */
    public function history(Request $request, CurrentRider $current): View
    {
        $rider = $current->get();
        abort_unless($rider, 404);

        $status = in_array($request->query('status'), ['pending', 'verified', 'rejected'], true)
            ? $request->query('status')
            : null;

        $rides = $rider->rides()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('ride_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $counts = $rider->rides()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('frontend.pages.history', compact('rides', 'status', 'counts'));
    }
}
