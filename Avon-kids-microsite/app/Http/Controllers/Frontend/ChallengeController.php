<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Support\CurrentRider;
use Illuminate\View\View;

class ChallengeController extends Controller
{
    public function index(CurrentRider $current): View
    {
        $rider = $current->get();
        $joined = $rider ? $rider->challenges()->get()->keyBy('id') : collect();

        $challenges = Challenge::active()->get()->map(function (Challenge $challenge) use ($joined) {
            $pivot = $joined->get($challenge->id)?->pivot;
            $progress = (float) ($pivot->progress_value ?? 0);

            $challenge->is_joined = (bool) $pivot;
            $challenge->progress = $progress;
            $challenge->percent = $challenge->target_value > 0
                ? (int) min(100, floor($progress / $challenge->target_value * 100))
                : 0;

            return $challenge;
        });

        return view('frontend.pages.challenges', compact('challenges'));
    }
}
