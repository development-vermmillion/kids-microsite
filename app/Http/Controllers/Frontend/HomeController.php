<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Faq;
use App\Models\Ride;
use App\Models\Rider;
use App\Models\Setting;
use App\Support\CurrentRider;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(CurrentRider $current): View
    {
        $rider = $current->get();
        $riderBadges = $rider ? $rider->badges()->get()->keyBy('id') : collect();

        // Hall of Fame: top 5 riders by verified distance.
        $leaderboard = Rider::query()
            ->where('is_active', true)
            ->withSum(['rides as total_km' => fn ($q) => $q->verified()], 'distance_km')
            ->orderByDesc('total_km')
            ->take(5)
            ->get();

        $unlocked = $riderBadges->filter(fn ($b) => $b->pivot->unlocked_at);

        $nextBadge = $riderBadges
            ->reject(fn ($b) => $b->pivot->unlocked_at)
            ->sortByDesc(fn ($b) => $b->pivot->progress_percent)
            ->first();

        $communityGoal = (float) Setting::get('community_goal_km', 150);
        $communityProgress = (float) Setting::get('community_progress_km', 0);

        return view('frontend.pages.home', [
            'leaderboard' => $leaderboard,
            'totalRides' => $rider ? $rider->rides()->where('status', Ride::STATUS_VERIFIED)->count() : 0,
            'milestonesCount' => $unlocked->count(),
            'recentMilestones' => $unlocked->sortByDesc(fn ($b) => $b->pivot->unlocked_at)->take(3),
            'nextBadge' => $nextBadge,
            'homeBadges' => Badge::where('show_on_home', true)->orderBy('sort_order')->get(),
            'riderBadges' => $riderBadges,
            'communityMiles' => (int) Setting::get('community_miles_today', 0),
            'communityGoal' => $communityGoal,
            'communityPercent' => $communityGoal > 0 ? min(100, (int) round($communityProgress / $communityGoal * 100)) : 0,
            'faqs' => Faq::active()->get(),
            'footerVariant' => 'home',
        ]);
    }
}
