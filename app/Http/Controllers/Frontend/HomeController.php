<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Faq;
use App\Models\Ride;
use App\Models\Rider;
use App\Models\Setting;
use App\Support\CurrentRider;
use App\Support\ProgressService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(CurrentRider $current, ProgressService $service): View
    {
        $rider = $current->get();
        $riderBadges = $rider ? $rider->badges()->get()->keyBy('id') : collect();
        $allBadges = Badge::orderBy('sort_order')->get();

        // Hall of Fame: top 5 riders by verified distance.
        $leaderboard = Rider::query()
            ->where('is_active', true)
            ->withSum(['rides as total_km' => fn ($q) => $q->verified()], 'distance_km')
            ->orderByDesc('total_km')
            ->take(5)
            ->get();

        $unlocked = $riderBadges->filter(fn ($b) => $b->pivot->unlocked_at);

        // Next badge: the ride-based badge the rider is closest to (see ProgressService::nextBadge).
        $nextBadge = $rider ? $service->nextBadge($rider) : null;

        $communityGoal = (float) Setting::get('community_goal_km', 150);
        $communityProgress = (float) Setting::get('community_progress_km', 0);

        return view('frontend.pages.home', [
            'leaderboard' => $leaderboard,
            'rider' => $rider,
            'totalRides' => $rider ? $rider->rides()->where('status', Ride::STATUS_VERIFIED)->count() : 0,
            'pendingRides' => $rider ? $rider->rides()->where('status', Ride::STATUS_PENDING)->count() : 0,
            'milestonesCount' => $unlocked->count(),
            'recentMilestones' => $unlocked->sortByDesc(fn ($b) => $b->pivot->unlocked_at)->take(3),
            'nextBadge' => $nextBadge,
            'homeBadges' => $allBadges->where('show_on_home', true)->values(),
            'riderBadges' => $riderBadges,
            'communityMiles' => (int) Setting::get('community_miles_today', 0),
            'communityGoal' => $communityGoal,
            'communityPercent' => $communityGoal > 0 ? min(100, (int) round($communityProgress / $communityGoal * 100)) : 0,
            'faqs' => Faq::active()->get(),
            'footerVariant' => 'home',
        ]);
    }
}
