@extends('frontend.layouts.base')

@section('title', $mode === 'join' ? 'Join the Adventure' : 'Login')
@section('body_class', 'login-page-design')

@section('body')
    <!-- Decorative Floating Elements -->
    <div class="decorative-float float-star">
        <span class="material-symbols-outlined icon" style="font-variation-settings: 'FILL' 1;">star</span>
    </div>
    <div class="decorative-float float-bolt">
        <span class="material-symbols-outlined icon" style="font-variation-settings: 'FILL' 1;">bolt</span>
    </div>
    <div class="decorative-float float-bike">
        <span class="material-symbols-outlined icon" style="font-variation-settings: 'FILL' 1;">pedal_bike</span>
    </div>
    <div class="decorative-float float-toys">
        <span class="material-symbols-outlined icon" style="font-variation-settings: 'FILL' 1;">toys</span>
    </div>

    <!-- Main Login Card -->
    <main class="login-card soft-ambient-depth">

        <!-- Form Side -->
        <div class="form-side">
            <div class="form-header">
                <a href="{{ route('home') }}" class="icon-circle shadow-sm" style="width: 100px; height: auto; background-color: #fff;">
                    <img src="{{ asset('frontend/images/avon-new-logo.jpeg') }}"
                        style="width: 100px; height: auto; background-color: #fff;" alt="Avon">
                </a>
                @if ($mode === 'join')
                    <h1 class="font-headline-lg">Join the Adventure!</h1>
                    <p class="font-body-lg">Create your rider account. We'll email you a code to check it's really you.</p>
                @else
                    <h1 class="font-headline-lg">Welcome Back, Rider!</h1>
                    <p class="font-body-lg">Ready for another adventure?</p>
                @endif
            </div>

            @foreach (['success' => 'celebration', 'info' => 'info', 'error' => 'error'] as $type => $icon)
                @if (session($type))
                    <div class="site-flash login-flash">
                        <div class="flash flash-{{ $type === 'error' ? 'error' : 'success' }}">
                            <span class="material-symbols-outlined">{{ $icon }}</span> {{ session($type) }}
                        </div>
                    </div>
                @endif
            @endforeach

            <form class="login-form" method="POST" action="{{ $mode === 'join' ? route('join.attempt') : route('login.attempt') }}"
                data-otp-form data-purpose="{{ $mode === 'join' ? 'register' : 'login' }}" novalidate>
                @csrf
                <x-bot-guard />

                @if ($mode === 'join')
                    <div class="input-group @error('name') has-error @enderror">
                        <label class="font-label-lg" for="name">Rider Name</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <span class="material-symbols-outlined">person</span>
                            </span>
                            <input id="name" name="name" placeholder="Enter your name" required type="text"
                                maxlength="100" value="{{ old('name') }}" class="font-body-lg" autocomplete="name" />
                        </div>
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group @error('username') has-error @enderror">
                        <label class="font-label-lg" for="username">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <span class="material-symbols-outlined">alternate_email</span>
                            </span>
                            <input id="username" name="username" placeholder="e.g. speedy_alex" required type="text"
                                maxlength="20" value="{{ old('username') }}" class="font-body-lg" autocomplete="username"
                                autocapitalize="none" spellcheck="false" />
                        </div>
                        @error('username')
                            <span class="field-error">{{ $message }}</span>
                        @else
                            <span class="field-hint">3–20 letters, numbers, dots or underscores. This is how you appear on the site.</span>
                        @enderror
                    </div>

                    <div class="input-group @error('mobile') has-error @enderror">
                        <label class="font-label-lg" for="mobile">Mobile Number</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <span class="material-symbols-outlined">phone</span>
                            </span>
                            <input id="mobile" name="mobile" placeholder="Parent's mobile number" required type="tel"
                                inputmode="numeric" maxlength="14" autocomplete="tel" value="{{ old('mobile') }}"
                                class="font-body-lg" />
                        </div>
                        @error('mobile')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                @endif

                <div class="input-group @error('email') has-error @enderror">
                    <label class="font-label-lg" for="email">Gmail / Email ID</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <span class="material-symbols-outlined">mail</span>
                        </span>
                        <input id="email" name="email" placeholder="you@gmail.com" required type="email"
                            maxlength="120" autocomplete="email" autocapitalize="none" spellcheck="false"
                            value="{{ old('email', $prefillEmail ?? '') }}" class="font-body-lg" />
                    </div>
                    @error('email')
                        <span class="field-error">{{ $message }}
                            @if ($mode === 'login' && str_contains($message, 'Join'))
                                <a href="{{ route('join', ['email' => old('email')]) }}">Join now</a>
                            @elseif ($mode === 'join' && str_contains($message, 'log in'))
                                <a href="{{ route('login') }}">Log in</a>
                            @endif
                        </span>
                    @enderror
                </div>
                <div class="form-options">
                    <a class="forgot-password font-label-lg" href="#" data-send-otp="{{ route('otp.send') }}"
                        data-status="#otp-status">Send OTP to my email</a>
                </div>
                <p class="otp-status" id="otp-status" role="status">{{ session('otp_sent') }}</p>

                <div class="input-group @error('otp') has-error @enderror">
                    <label class="font-label-lg" for="otp">Email OTP</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <span class="material-symbols-outlined">key</span>
                        </span>
                        <input id="otp" name="otp" placeholder="6-digit code from your email" required type="text"
                            inputmode="numeric" maxlength="6" autocomplete="one-time-code" class="font-body-lg" />
                    </div>
                    @error('otp')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                @if (\App\Support\OtpService::testCode())
                    <p class="otp-test-note">Testing mode: no email is sent and the OTP is always <strong>{{ \App\Support\OtpService::testCode() }}</strong></p>
                @endif

                <x-turnstile :action="$mode === 'join' ? 'register' : 'login'" />

                <button class="login-btn font-headline-sm chunky-shadow-btn" type="submit">
                    {{ $mode === 'join' ? 'Create My Account' : "Let's Ride!" }}
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">pedal_bike</span>
                </button>
            </form>

            <div class="join-prompt">
                <p class="font-label-lg">
                    @if ($mode === 'join')
                        Already a rider? <a href="{{ route('login') }}">Log in</a>
                    @else
                        New rider? <a href="{{ route('join') }}">Join the Adventure</a>
                    @endif
                </p>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('frontend/js/otp.js') }}"></script>
@endpush
