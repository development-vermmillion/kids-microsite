<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Support\CurrentRider;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(CurrentRider $current): View
    {
        $rider = $current->get();
        $riderBadges = $rider ? $rider->badges()->get()->keyBy('id') : collect();

        $trophies = Badge::where('show_in_trophy_room', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Badge $badge) use ($riderBadges) {
                $pivot = $riderBadges->get($badge->id)?->pivot;

                $badge->unlocked_at = $pivot?->unlocked_at ? Carbon::parse($pivot->unlocked_at) : null;
                $badge->progress_percent = (int) ($pivot->progress_percent ?? 0);

                return $badge;
            });

        return view('frontend.pages.trophies', compact('trophies'));
    }
}
