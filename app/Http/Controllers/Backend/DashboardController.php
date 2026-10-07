<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Ride;
use App\Models\Rider;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('backend.dashboard', [
            'stats' => [
                'riders' => Rider::count(),
                'pending' => Ride::where('status', Ride::STATUS_PENDING)->count(),
                'verifiedKm' => (float) Ride::verified()->sum('distance_km'),
                'activeChallenges' => Challenge::where('is_active', true)->count(),
                'ridesThisWeek' => Ride::where('created_at', '>=', now()->startOfWeek())->count(),
                'newRidersThisWeek' => Rider::where('created_at', '>=', now()->startOfWeek())->count(),
            ],
            'pendingRides' => Ride::with('rider')
                ->where('status', Ride::STATUS_PENDING)
                ->oldest()
                ->take(6)
                ->get(),
            'topRiders' => Rider::withSum(['rides as total_km' => fn ($q) => $q->verified()], 'distance_km')
                ->orderByDesc('total_km')
                ->take(5)
                ->get(),
            'recentRides' => Ride::with('rider')
                ->whereIn('status', [Ride::STATUS_VERIFIED, Ride::STATUS_REJECTED])
                ->latest('reviewed_at')
                ->latest('id')
                ->take(5)
                ->get(),
        ]);
    }
}
