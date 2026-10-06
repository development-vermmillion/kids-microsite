@extends('frontend.layouts.app')

@section('title', 'Upload Ride')
@section('body_class', 'ride-page')

@section('content')
    <!-- Upload Form Content -->
    <div class="upload-content">
        <div class="upload-card soft-shadow">
            <div class="form-header">
                <div class="icon-circle shadow-sm">
                    <span class="material-symbols-outlined icon" style="font-variation-settings: 'FILL' 1;">upload_file</span>
                </div>
                <h1 class="font-headline-lg">Upload Ride</h1>
                <p class="font-body-lg text-variant">Share your latest adventure to earn points and badges!</p>
            </div>

            {{-- TODO: switch to method="POST" + enctype="multipart/form-data" with a rides.store route. --}}
            <form class="upload-form" action="{{ route('home') }}">
                <div class="form-grid">
                    <div class="input-group">
                        <label class="font-label-lg" for="mobile">Mobile Number</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">smartphone</span>
                            <input id="mobile" name="mobile" type="tel" placeholder="Enter mobile number" required
                                value="{{ $currentRider?->mobile }}" class="font-body-lg" />
                        </div>
                    </div>

                    <div class="input-group otp-group">
                        <label class="font-label-lg" for="otp">OTP Verification</label>
                        <div class="otp-wrapper">
                            <div class="input-wrapper">
                                <span class="input-icon material-symbols-outlined">password</span>
                                <input id="otp" name="otp" type="text" placeholder="Enter OTP" required class="font-body-lg" />
                            </div>
                            <button type="button" class="btn-send-otp font-label-lg chunky-shadow-small">Send OTP</button>
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label class="font-label-lg" for="name">Rider Name</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">person</span>
                            <input id="name" name="name" type="text" placeholder="Enter rider name" required
                                value="{{ $currentRider?->name }}" class="font-body-lg" />
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="font-label-lg" for="date">Ride Date</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">calendar_today</span>
                            <input id="date" name="ride_date" type="date" required max="{{ now()->toDateString() }}"
                                class="font-body-lg" />
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="font-label-lg" for="distance">Ride Distance (km)</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">directions_bike</span>
                            <input id="distance" name="distance_km" type="number" step="0.1" placeholder="e.g. 5.5"
                                required class="font-body-lg" />
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label class="font-label-lg" for="time">Ride Time</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">timer</span>
                            <input id="time" name="ride_time" type="time" required class="font-body-lg" />
                        </div>
                    </div>

                    <div class="input-group full-width file-upload-group">
                        <label class="font-label-lg" for="proof">Photo Proof</label>
                        <div class="file-upload-wrapper">
                            <input id="proof" name="proof_image" type="file" accept="image/*" class="file-input" />
                            <div class="upload-placeholder">
                                <span class="material-symbols-outlined icon">add_photo_alternate</span>
                                <p class="font-body-md text-main">Click to upload screenshot or photo</p>
                                <p class="font-label-sm text-variant">Supports JPG, PNG</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="submit-btn font-headline-sm chunky-shadow-btn" type="submit">
                    Submit Ride
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">send</span>
                </button>
            </form>
        </div>
    </div>
@endsection
