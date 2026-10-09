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
                <x-bot-guard />
                <div class="form-grid">
                    <div class="input-group full-width">
                        <div class="uploading-as">
                            <img src="{{ $currentRider->avatar_or_placeholder }}" alt="" />
                            <div>
                                <small>Uploading as</small>
                                <strong>{{ $currentRider->name }}</strong>
                                <span>{{ $currentRider->username ? '@'.$currentRider->username : $currentRider->email }}</span>
                            </div>
                            <a href="{{ route('logout') }}">Not you?</a>
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <div class="daily-limit {{ $uploadsLeft === 0 ? 'is-full' : '' }}">
                            <span class="material-symbols-outlined">{{ $uploadsLeft === 0 ? 'block' : 'event_available' }}</span>
                            @if ($uploadsLeft === 0)
                                <p>You've uploaded {{ $dailyLimit }} rides today, which is today's limit. Come back tomorrow to upload more!</p>
                            @else
                                <p>Rides uploaded today: <strong>{{ $uploadedToday }} of {{ $dailyLimit }}</strong>.
                                    You can upload {{ $uploadsLeft }} more {{ $uploadsLeft === 1 ? 'ride' : 'rides' }} today.</p>
                            @endif
                        </div>
                    </div>

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

                <button class="submit-btn font-headline-sm chunky-shadow-btn" type="submit" @disabled($uploadsLeft === 0)>
                    Submit Ride
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">send</span>
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
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
