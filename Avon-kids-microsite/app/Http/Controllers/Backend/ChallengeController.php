<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Challenge;
use App\Support\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChallengeController extends Controller
{
    public function index(): View
    {
        $challenges = Challenge::withCount('riders')
            ->withCount(['riders as completed_count' => fn ($q) => $q->whereNotNull('challenge_rider.completed_at')])
            ->orderBy('sort_order')
            ->get();

        return view('backend.challenges.index', compact('challenges'));
    }

    public function create(): View
    {
        return view('backend.challenges.form', [
            'challenge' => new Challenge([
                'icon' => 'flag',
                'color' => 'primary',
                'progress_label' => 'Progress',
                'metric' => 'distance',
                'unit' => 'km',
                'is_active' => true,
                'sort_order' => (Challenge::max('sort_order') ?? 0) + 1,
            ]),
            'badges' => $this->badgeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Challenge::create($this->validated($request));

        return redirect()->route('admin.challenges.index')->with('success', 'Challenge added.');
    }

    public function edit(Challenge $challenge): View
    {
        return view('backend.challenges.form', [
            'challenge' => $challenge,
            'badges' => $this->badgeOptions(),
        ]);
    }

    public function update(Request $request, Challenge $challenge): RedirectResponse
    {
        $challenge->update($this->validated($request));

        // Goal, dates or rule may have changed: refresh everyone who joined.
        foreach ($challenge->riders as $rider) {
            app(ProgressService::class)->recalculate($rider);
        }

        return redirect()->route('admin.challenges.index')->with('success', 'Challenge updated.');
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $challenge->delete();

        return redirect()->route('admin.challenges.index')->with('success', 'Challenge deleted.');
    }

    private function badgeOptions(): array
    {
        return Badge::orderBy('sort_order')->pluck('name', 'id')->all();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'color' => ['required', Rule::in(['primary', 'secondary', 'tertiary'])],
            'reward_points' => ['required', 'integer', 'min:0'],
            'target_value' => ['required', 'numeric', 'gt:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'progress_label' => ['required', 'string', 'max:40'],
            'metric' => ['required', Rule::in(array_keys(ProgressService::METRICS))],
            'badge_id' => ['nullable', 'exists:badges,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [
            'icon.regex' => 'Use the icon name in lowercase with underscores, e.g. directions_bike.',
        ]);
    }
}
