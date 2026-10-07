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
    public function index(CurrentRider $current, ProgressService $service): View
    {
        $rider = $current->get();
        $joined = $rider ? $rider->challenges()->get()->keyBy('id') : collect();

        $challenges = Challenge::running()->get()->map(function (Challenge $challenge) use ($joined, $rider, $service) {
            $joinedChallenge = $joined->get($challenge->id);
            $pivot = $joinedChallenge?->pivot;
            $progress = (float) ($pivot->progress_value ?? 0);
            $target = (float) $challenge->target_value;

            // Rides uploaded but not yet reviewed (shown as a striped part of the bar).
            $pending = ($rider && $joinedChallenge && ! $pivot->completed_at)
                ? $service->pendingForChallenge($rider, $joinedChallenge)
                : 0.0;

            $challenge->is_joined = (bool) $pivot;
            $challenge->is_completed = (bool) $pivot?->completed_at;
            $challenge->progress = $progress;
            $challenge->pending = $pending;
            $challenge->percent = $target > 0 ? (int) min(100, floor($progress / $target * 100)) : 0;
            $challenge->pending_percent = $target > 0 ? (int) min(100 - $challenge->percent, ceil($pending / $target * 100)) : 0;

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
