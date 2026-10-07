<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\Rider;
use App\Models\Setting;
use App\Support\CurrentRider;
use App\Support\OtpService;
use App\Support\ProgressService;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RideController extends Controller
{
    public function create(): View
    {
        return view('frontend.pages.upload-ride');
    }

    /**
     * Saves an uploaded ride.
     *  - Logged-in riders just fill in the ride.
     *  - Guests confirm their mobile number with an OTP; a new number creates a
     *    rider, and they are logged in afterwards.
     * The ride waits for admin review, unless "count rides straight away" is on
     * in the admin's Site settings.
     */
    public function store(Request $request, CurrentRider $current, OtpService $otp, ProgressService $progress): RedirectResponse
    {
        $rider = $current->get();

        $rules = [
            'ride_date' => ['required', 'date', 'before_or_equal:today', 'after:'.now()->subYear()->toDateString()],
            'ride_time' => ['required', 'date_format:H:i'],
            'distance_km' => ['required', 'numeric', 'min:0.1', 'max:300'],
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        if (! $rider) {
            $request->merge(['mobile' => OtpService::normalise($request->input('mobile'))]);
            $rules += [
                'mobile' => AuthController::MOBILE_RULE,
                'otp' => ['required', 'string', 'max:10'],
                'name' => ['required', 'string', 'max:100'],
            ];
        }

        $data = $request->validate($rules, [
            'mobile.regex' => AuthController::MOBILE_MESSAGE,
            'otp.required' => 'Please enter the OTP sent to your mobile.',
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
            'otp' => 'OTP',
            'ride_date' => 'ride date',
            'ride_time' => 'ride time',
            'distance_km' => 'ride distance',
        ]);

        if (! $rider) {
            $existing = Rider::where('mobile', $data['mobile'])->first();

            if ($existing && ! $existing->is_active) {
                return back()->withInput()->with('error', 'This account is paused. Please contact '.Setting::get('support_email', 'avon@avoncycles.com').'.');
            }

            if ($error = $otp->check($data['mobile'], $data['otp'])) {
                throw ValidationException::withMessages(['otp' => $error]);
            }

            $rider = $existing ?? Rider::create(['mobile' => $data['mobile'], 'name' => $data['name']]);
            $current->login($rider);
        }

        $duplicate = $rider->rides()
            ->whereDate('ride_date', $data['ride_date'])
            ->where('distance_km', $data['distance_km'])
            ->where('status', '!=', Ride::STATUS_REJECTED)
            ->exists();

        if ($duplicate) {
            return redirect()->route('rides.create')->withInput($request->except('otp'))
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
}
