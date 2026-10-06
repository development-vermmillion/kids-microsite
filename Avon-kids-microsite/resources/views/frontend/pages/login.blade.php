@extends('frontend.layouts.base')

@section('title', 'Login')
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
                <div class="icon-circle shadow-sm" style="width: 100px; height: auto; background-color: #fff;">
                    <img src="{{ asset('frontend/images/avon-new-logo.jpeg') }}"
                        style="width: 100px; height: auto; background-color: #fff;" alt="Avon">
                </div>
                <h1 class="font-headline-lg">Welcome Back, Rider!</h1>
                <p class="font-body-lg">Ready for another adventure?</p>
            </div>

            {{-- TODO: post to a login route that verifies the OTP once OTP login is built. --}}
            <form class="login-form" action="{{ route('home') }}">
                <div class="input-group">
                    <label class="font-label-lg" for="username">Mobile Number</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <span class="material-symbols-outlined">phone</span>
                        </span>
                        <input id="username" name="username" placeholder="Enter your mobile number" required=""
                            type="text" class="font-body-lg" />
                    </div>
                </div>
                <div class="form-options">
                    <a class="forgot-password font-label-lg" href="#">Send OTP</a>
                </div>

                <div class="input-group">
                    <label class="font-label-lg" for="password">OTP</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <span class="material-symbols-outlined">key</span>
                        </span>
                        <input id="password" name="password" placeholder="Enter OTP" required="" type="password"
                            class="font-body-lg" />
                    </div>
                </div>

                <div class="form-options">
                    <a class="forgot-password font-label-lg" href="{{ route('home') }}">Bypass</a>
                </div>

                <button class="login-btn font-headline-sm chunky-shadow-btn" type="submit">
                    Let's Ride!
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">pedal_bike</span>
                </button>
            </form>

            <div class="join-prompt">
                <p class="font-label-lg">
                    New rider? <a href="#">Join the Adventure</a>
                </p>
            </div>
        </div>
    </main>
@endsection
