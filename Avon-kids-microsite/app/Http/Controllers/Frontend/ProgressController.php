<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\CurrentRider;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function index(CurrentRider $current): View
    {
        $rider = $current->get();
        abort_unless($rider, 404);

        $verified = $rider->rides()->verified();

        return view('frontend.pages.progress', [
            'totalDistance' => (float) (clone $verified)->sum('distance_km'),
            'totalRides' => (clone $verified)->count(),
            'badgesEarned' => $rider->badges()->wherePivotNotNull('unlocked_at')->count(),
            'hoursInSaddle' => (int) round((clone $verified)->sum('duration_minutes') / 60),
            'recentRides' => $rider->rides()->orderByDesc('ride_date')->latest('id')->take(4)->get(),
        ]);
    }
}
