<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\Rider;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RiderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $sort = $request->query('sort', 'km');

        $riders = Rider::query()
            ->withSum(['rides as total_km' => fn ($q) => $q->verified()], 'distance_km')
            ->withCount('rides')
            ->withCount(['rides as pending_count' => fn ($q) => $q->where('status', 'pending')])
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")))
            ->when($sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($sort === 'newest', fn ($q) => $q->latest())
            ->when($sort === 'km', fn ($q) => $q->orderByDesc('total_km'))
            ->paginate(25)
            ->withQueryString();

        return view('backend.riders.index', compact('riders', 'search', 'sort'));
    }

    public function show(Rider $rider): View
    {
        $verified = $rider->rides()->verified();

        return view('backend.riders.show', [
            'rider' => $rider,
            'stats' => [
                'km' => (float) (clone $verified)->sum('distance_km'),
                'verified' => (clone $verified)->count(),
                'pending' => $rider->rides()->where('status', 'pending')->count(),
                'minutes' => (int) (clone $verified)->sum('duration_minutes'),
            ],
            'rides' => $rider->rides()->orderByDesc('ride_date')->latest('id')->take(15)->get(),
            'badges' => Badge::orderBy('sort_order')->get(),
            'riderBadges' => $rider->badges()->get()->keyBy('id'),
            'challenges' => Challenge::orderBy('sort_order')->get(),
            'riderChallenges' => $rider->challenges()->get()->keyBy('id'),
        ]);
    }

    public function create(): View
    {
        return view('backend.riders.form', ['rider' => new Rider(['level' => 1, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['avatar'] = $request->hasFile('avatar') ? Uploads::store($request->file('avatar'), 'avatars') : null;

        $rider = Rider::create($data);

        return redirect()->route('admin.riders.show', $rider)->with('success', 'Rider added.');
    }

    public function edit(Rider $rider): View
    {
        return view('backend.riders.form', compact('rider'));
    }

    public function update(Request $request, Rider $rider): RedirectResponse
    {
        $data = $this->validated($request, $rider);

        if ($request->hasFile('avatar') || $request->boolean('remove_avatar')) {
            Uploads::delete($rider->avatar);
            $data['avatar'] = $request->hasFile('avatar') ? Uploads::store($request->file('avatar'), 'avatars') : null;
        }

        $rider->update($data);

        return redirect()->route('admin.riders.show', $rider)->with('success', 'Rider updated.');
    }

    public function destroy(Rider $rider): RedirectResponse
    {
        foreach ($rider->rides as $ride) {
            Uploads::delete($ride->proof_image);
        }
        Uploads::delete($rider->avatar);
        $rider->delete();

        return redirect()->route('admin.riders.index')->with('success', 'Rider and all their rides deleted.');
    }

    /** Unlock badges or set progress towards them. Newly unlocked badges notify the rider. */
    public function updateBadges(Request $request, Rider $rider): RedirectResponse
    {
        $request->validate([
            'badges' => ['array'],
            'badges.*.progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'badges.*.unlocked_at' => ['nullable', 'date'],
        ]);

        $current = $rider->badges()->get()->keyBy('id');
        $sync = [];
        $newlyUnlocked = [];

        foreach (Badge::all() as $badge) {
            $input = $request->input("badges.{$badge->id}", []);
            $unlocked = ! empty($input['unlocked']);
            $progress = (int) ($input['progress'] ?? 0);

            if (! $unlocked && $progress === 0) {
                continue; // detached
            }

            $unlockedAt = null;
            if ($unlocked) {
                $unlockedAt = ! empty($input['unlocked_at'])
                    ? Carbon::parse($input['unlocked_at'])
                    : ($current->get($badge->id)?->pivot->unlocked_at ?? now());

                if (! $current->get($badge->id)?->pivot->unlocked_at) {
                    $newlyUnlocked[] = $badge;
                }
            }

            $sync[$badge->id] = [
                'unlocked_at' => $unlockedAt,
                'progress_percent' => $unlocked ? 100 : $progress,
            ];
        }

        $rider->badges()->sync($sync);

        foreach ($newlyUnlocked as $badge) {
            $rider->notifications()->create([
                'title' => 'New Badge Unlocked!',
                'message' => "You earned the \"{$badge->name}\" badge. {$badge->description}",
                'icon' => 'stars',
                'color' => 'primary',
            ]);
        }

        return redirect()->route('admin.riders.show', $rider)
            ->with('success', 'Badges saved.'.(count($newlyUnlocked) ? ' The rider was notified about '.count($newlyUnlocked).' new badge(s).' : ''));
    }

    /** Join riders to challenges and set their progress. */
    public function updateChallenges(Request $request, Rider $rider): RedirectResponse
    {
        $request->validate([
            'challenges' => ['array'],
            'challenges.*.progress' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sync = [];
        foreach (Challenge::all() as $challenge) {
            $input = $request->input("challenges.{$challenge->id}", []);
            if (empty($input['joined'])) {
                continue;
            }

            $progress = (float) ($input['progress'] ?? 0);
            $sync[$challenge->id] = [
                'progress_value' => $progress,
                'completed_at' => $progress >= $challenge->target_value ? now() : null,
            ];
        }

        $rider->challenges()->sync($sync);

        return redirect()->route('admin.riders.show', $rider)->with('success', 'Challenge progress saved.');
    }

    private function validated(Request $request, ?Rider $rider = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^[0-9+\- ]{8,15}$/', Rule::unique('riders', 'mobile')->ignore($rider?->id)],
            'level' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [
            'mobile.regex' => 'Enter a valid mobile number (digits only, 8–15 characters).',
        ]);
    }
}
