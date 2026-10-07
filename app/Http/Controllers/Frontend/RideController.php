<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\Rider;
use App\Models\Setting;
use App\Support\CurrentRider;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RideController extends Controller
{
    public function create(): View
    {
        return view('frontend.pages.upload-ride');
    }

    /**
     * Saves an uploaded ride as "pending" for admin review.
     * The rider is found by mobile number (a new rider is created for a new number).
     * TODO (OTP): check the OTP for this mobile number before saving.
     */
    public function store(Request $request, CurrentRider $current): RedirectResponse
    {
        $request->merge(['mobile' => preg_replace('/[^0-9+]/', '', (string) $request->input('mobile'))]);

        $data = $request->validate([
            'mobile' => ['required', 'regex:/^\+?[0-9]{10,13}$/'],
            'name' => ['required', 'string', 'max:100'],
            'ride_date' => ['required', 'date', 'before_or_equal:today', 'after:'.now()->subYear()->toDateString()],
            'ride_time' => ['required', 'date_format:H:i'],
            'distance_km' => ['required', 'numeric', 'min:0.1', 'max:300'],
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'otp' => ['nullable', 'string', 'max:10'],
        ], [
            'mobile.regex' => 'Please enter a valid 10-digit mobile number.',
            'ride_date.before_or_equal' => 'The ride date can’t be in the future.',
            'ride_date.after' => 'Rides older than a year can’t be uploaded.',
            'distance_km.min' => 'The distance must be at least 0.1 km.',
            'distance_km.max' => 'That distance looks too long for one ride. Please check it.',
            'proof_image.required' => 'Please add a screenshot or photo of your ride.',
            'proof_image.image' => 'The proof must be a picture (JPG or PNG).',
            'proof_image.max' => 'The picture is too big. Please use one under 5 MB.',
        ], [
            'mobile' => 'mobile number',
            'name' => 'rider name',
            'ride_date' => 'ride date',
            'ride_time' => 'ride time',
            'distance_km' => 'ride distance',
        ]);

        $rider = Rider::firstOrCreate(['mobile' => $data['mobile']], ['name' => $data['name']]);

        if (! $rider->is_active) {
            return back()->withInput()->with('error', 'This account is paused. Please contact '.Setting::get('support_email', 'avon@avoncycles.com').'.');
        }

        $duplicate = $rider->rides()
            ->whereDate('ride_date', $data['ride_date'])
            ->where('distance_km', $data['distance_km'])
            ->where('status', '!=', Ride::STATUS_REJECTED)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'You have already uploaded a ride with this date and distance.');
        }

        $rider->rides()->create([
            'ride_date' => $data['ride_date'],
            'ride_time' => $data['ride_time'],
            'distance_km' => $data['distance_km'],
            'proof_image' => Uploads::store($request->file('proof_image'), 'rides'),
            'status' => Ride::STATUS_PENDING,
        ]);

        $message = 'Ride submitted! We’ll check it soon and your progress will update once it is verified.';

        // Riders uploading for their own account go to their progress page.
        return $current->get()?->is($rider)
            ? redirect()->route('progress')->with('success', $message)
            : redirect()->route('rides.create')->with('success', $message);
    }
}
