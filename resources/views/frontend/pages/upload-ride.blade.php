@extends('frontend.layouts.app')

@section('title', 'Upload Ride')
@section('body_class', 'ride-page')

@php
    // Small helper to add the error state + message to a field.
    $err = fn (string $field) => $errors->has($field) ? 'has-error' : '';
@endphp

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

            <form class="upload-form" method="POST" action="{{ route('rides.store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="form-grid">
                    @if ($currentRider)
                        {{-- Logged in: no OTP needed. --}}
                        <div class="input-group full-width">
                            <div class="uploading-as">
                                <img src="{{ $currentRider->avatar_or_placeholder }}" alt="" />
                                <div>
                                    <small>Uploading as</small>
                                    <strong>{{ $currentRider->name }}</strong>
                                    <span>{{ $currentRider->mobile }}</span>
                                </div>
                                <a href="{{ route('logout') }}">Not you?</a>
                            </div>
                        </div>
                    @else
                        <div class="input-group {{ $err('mobile') }}">
                            <label class="font-label-lg" for="mobile">Mobile Number</label>
                            <div class="input-wrapper">
                                <span class="input-icon material-symbols-outlined">smartphone</span>
                                <input id="mobile" name="mobile" type="tel" placeholder="Enter mobile number" required
                                    inputmode="numeric" maxlength="14" value="{{ old('mobile') }}" class="font-body-lg" />
                            </div>
                            @error('mobile')<span class="field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="input-group otp-group {{ $err('otp') }}">
                            <label class="font-label-lg" for="otp">OTP Verification</label>
                            <div class="otp-wrapper">
                                <div class="input-wrapper">
                                    <span class="input-icon material-symbols-outlined">password</span>
                                    <input id="otp" name="otp" type="text" placeholder="Enter OTP" inputmode="numeric"
                                        maxlength="6" autocomplete="one-time-code" class="font-body-lg" />
                                </div>
                                <button type="button" class="btn-send-otp font-label-lg chunky-shadow-small"
                                    data-send-otp="{{ route('otp.send') }}" data-mobile-input="#mobile"
                                    data-status="#otp-status">Send OTP</button>
                            </div>
                            <span class="otp-status" id="otp-status" role="status"></span>
                            @error('otp')<span class="field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="input-group full-width {{ $err('name') }}">
                            <label class="font-label-lg" for="name">Rider Name</label>
                            <div class="input-wrapper">
                                <span class="input-icon material-symbols-outlined">person</span>
                                <input id="name" name="name" type="text" placeholder="Enter rider name" required
                                    value="{{ old('name') }}" class="font-body-lg" />
                            </div>
                            @error('name')<span class="field-error">{{ $message }}</span>@enderror
                            <p class="form-note">
                                Already a rider? <a href="{{ route('login') }}">Log in</a> to skip this step.
                                @if (\App\Support\OtpService::testCode())
                                    <br>Testing mode: the OTP is always <strong>{{ \App\Support\OtpService::testCode() }}</strong>.
                                @endif
                            </p>
                        </div>
                    @endif

                    <div class="input-group {{ $err('ride_date') }}">
                        <label class="font-label-lg" for="date">Ride Date</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">calendar_today</span>
                            <input id="date" name="ride_date" type="date" required max="{{ now()->toDateString() }}"
                                value="{{ old('ride_date') }}" class="font-body-lg" />
                        </div>
                        @error('ride_date')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group {{ $err('distance_km') }}">
                        <label class="font-label-lg" for="distance">Ride Distance (km)</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">directions_bike</span>
                            <input id="distance" name="distance_km" type="number" step="0.1" min="0.1" placeholder="e.g. 5.5"
                                required value="{{ old('distance_km') }}" class="font-body-lg" />
                        </div>
                        @error('distance_km')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group full-width {{ $err('ride_time') }}">
                        <label class="font-label-lg" for="time">Ride Time</label>
                        <div class="input-wrapper">
                            <span class="input-icon material-symbols-outlined">timer</span>
                            <input id="time" name="ride_time" type="time" required value="{{ old('ride_time') }}"
                                class="font-body-lg" />
                        </div>
                        @error('ride_time')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group full-width file-upload-group {{ $err('proof_image') }}">
                        <label class="font-label-lg" for="proof">Photo Proof</label>
                        <div class="file-upload-wrapper" id="proof-wrapper">
                            <input id="proof" name="proof_image" type="file" accept="image/png,image/jpeg,image/webp"
                                class="file-input" required />
                            <div class="upload-placeholder">
                                <span class="material-symbols-outlined icon">add_photo_alternate</span>
                                <p class="font-body-md text-main">Click to upload screenshot or photo</p>
                                <p class="font-label-sm text-variant">Supports JPG, PNG</p>
                            </div>
                            <div class="file-chosen">
                                <img id="proof-preview" alt="" />
                                <div>
                                    <span id="proof-name"></span>
                                    <small>Tap to choose a different picture</small>
                                </div>
                            </div>
                        </div>
                        @error('proof_image')<span class="field-error">{{ $message }}</span>@enderror
                        @if ($errors->any() && ! $errors->has('proof_image'))
                            <p class="form-note">For safety, please choose your picture again.</p>
                        @endif
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

@push('scripts')
    <script src="{{ asset('frontend/js/otp.js') }}"></script>
    <script>
        // Show the chosen picture inside the upload box.
        document.getElementById('proof').addEventListener('change', (e) => {
            const file = e.target.files[0];
            const wrapper = document.getElementById('proof-wrapper');
            if (!file) {
                wrapper.classList.remove('has-file');
                return;
            }
            document.getElementById('proof-name').textContent = file.name;
            document.getElementById('proof-preview').src = URL.createObjectURL(file);
            wrapper.classList.add('has-file');
        });
    </script>
@endpush
