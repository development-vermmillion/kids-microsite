<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\CurrentRider;
use Illuminate\Http\Request;
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
            'totalUploaded' => $rider->rides()->count(),
        ]);
    }

    /** "View All History": every ride the rider uploaded, with a status filter. */
    public function history(Request $request, CurrentRider $current): View
    {
        $rider = $current->get();
        abort_unless($rider, 404);

        $status = in_array($request->query('status'), ['pending', 'verified', 'rejected'], true)
            ? $request->query('status')
            : null;

        $rides = $rider->rides()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('ride_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $counts = $rider->rides()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('frontend.pages.history', compact('rides', 'status', 'counts'));
    }
}
