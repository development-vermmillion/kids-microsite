<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Support\CurrentRider;
use App\Support\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChallengeController extends Controller
{
    public function index(CurrentRider $current): View
    {
        $rider = $current->get();
        $joined = $rider ? $rider->challenges()->get()->keyBy('id') : collect();

        $challenges = Challenge::running()->get()->map(function (Challenge $challenge) use ($joined) {
            $pivot = $joined->get($challenge->id)?->pivot;
            $progress = (float) ($pivot->progress_value ?? 0);

            $challenge->is_joined = (bool) $pivot;
            $challenge->is_completed = (bool) $pivot?->completed_at;
            $challenge->progress = $progress;
            $challenge->percent = $challenge->target_value > 0
                ? (int) min(100, floor($progress / $challenge->target_value * 100))
                : 0;

            return $challenge;
        });

        return view('frontend.pages.challenges', compact('challenges'));
    }

    public function join(Challenge $challenge, CurrentRider $current, ProgressService $progress): RedirectResponse
    {
        $rider = $current->get();
        abort_unless($rider, 403);

        if (! Challenge::running()->whereKey($challenge->id)->exists()) {
            return redirect()->route('challenges.index')->with('error', 'This challenge is not open right now.');
        }

        if (! $rider->challenges()->whereKey($challenge->id)->exists()) {
            $rider->challenges()->attach($challenge->id, ['progress_value' => 0]);
            // Rides already inside the challenge dates count straight away.
            $progress->recalculate($rider);
        }

        return redirect()->route('challenges.index')
            ->with('success', "You joined “{$challenge->title}”. Upload your rides to fill the bar!");
    }
}
