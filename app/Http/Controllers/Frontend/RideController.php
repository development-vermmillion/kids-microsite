<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\Rider;
use App\Models\Setting;
use App\Support\CurrentRider;
use App\Support\ProgressService;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RideController extends Controller
{
    public function create(CurrentRider $current): View
    {
        return view('frontend.pages.upload-ride', $this->dailyAllowance($current->get()));
    }

    /**
     * Saves an uploaded ride for the logged-in rider (guests are sent to log in first).
     * A rider can upload a limited number of rides per day (3 unless changed in
     * the admin's Site settings). The ride waits for admin review, unless
     * "count rides straight away" is on.
     */
    public function store(Request $request, CurrentRider $current, ProgressService $progress): RedirectResponse
    {
        $rider = $current->get();

        $allowance = $this->dailyAllowance($rider);
        if ($allowance['uploadsLeft'] === 0) {
            return redirect()->route('rides.create')
                ->with('error', "You can upload up to {$allowance['dailyLimit']} rides a day. Please come back tomorrow!");
        }

        $data = $request->validate([
            'ride_date' => ['required', 'date', 'before_or_equal:today', 'after:'.now()->subYear()->toDateString()],
            'ride_time' => ['required', 'date_format:H:i'],
            'distance_km' => ['required', 'numeric', 'min:0.1', 'max:300'],
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'ride_date.before_or_equal' => 'The ride date can’t be in the future.',
            'ride_date.after' => 'Rides older than a year can’t be uploaded.',
            'distance_km.min' => 'The distance must be at least 0.1 km.',
            'distance_km.max' => 'That distance looks too long for one ride. Please check it.',
            'proof_image.required' => 'Please add a screenshot or photo of your ride.',
            'proof_image.image' => 'The proof must be a picture (JPG or PNG).',
            'proof_image.max' => 'The picture is too big. Please use one under 5 MB.',
        ], [
            'ride_date' => 'ride date',
            'ride_time' => 'ride time',
            'distance_km' => 'ride distance',
        ]);

        $duplicate = $rider->rides()
            ->whereDate('ride_date', $data['ride_date'])
            ->where('distance_km', $data['distance_km'])
            ->where('status', '!=', Ride::STATUS_REJECTED)
            ->exists();

        if ($duplicate) {
            return redirect()->route('rides.create')->withInput()
                ->with('error', 'You have already uploaded a ride with this date and distance.');
        }

        $autoApprove = (bool) Setting::get('auto_approve_rides', false);

        $rider->rides()->create([
            'ride_date' => $data['ride_date'],
            'ride_time' => $data['ride_time'],
            'distance_km' => $data['distance_km'],
            'proof_image' => Uploads::store($request->file('proof_image'), 'rides'),
            'status' => $autoApprove ? Ride::STATUS_VERIFIED : Ride::STATUS_PENDING,
            'reviewed_at' => $autoApprove ? now() : null,
        ]);

        // Update challenge and badge progress straight away.
        $progress->recalculate($rider);

        return redirect()->route('progress')->with('success', $autoApprove
            ? 'Ride added! Your progress has been updated.'
            : 'Ride submitted! It shows as “waiting for review” until our team checks it.');
    }

    /** How many rides the rider has uploaded today (any status) and how many more are allowed. */
    private function dailyAllowance(Rider $rider): array
    {
        $limit = max(1, (int) Setting::get('max_rides_per_day', 3));
        $today = $rider->rides()->where('created_at', '>=', now()->startOfDay())->count();

        return [
            'dailyLimit' => $limit,
            'uploadedToday' => $today,
            'uploadsLeft' => max(0, $limit - $today),
        ];
    }
}
