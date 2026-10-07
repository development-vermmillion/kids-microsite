<?php

use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\ChallengeController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NotificationController;
use App\Http\Controllers\Frontend\ProgressController;
use App\Http\Controllers\Frontend\RideController;
use App\Http\Controllers\Frontend\SettingsController;
use App\Http\Controllers\Frontend\TrophyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend (rider-facing) routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Mobile number + OTP login / sign-up
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:rider-login');
Route::get('/join', [AuthController::class, 'showJoin'])->name('join');
Route::post('/join', [AuthController::class, 'join'])->name('join.attempt')->middleware('throttle:rider-login');
Route::post('/otp/send', [AuthController::class, 'sendOtp'])->name('otp.send')->middleware('throttle:otp');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Open to everyone (the upload form asks guests for mobile + OTP)
Route::get('/upload-ride', [RideController::class, 'create'])->name('rides.create');
Route::post('/upload-ride', [RideController::class, 'store'])->name('rides.store')->middleware('throttle:ride-upload');
Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');

// Logged-in riders only
Route::middleware('rider')->group(function () {
    Route::post('/challenges/{challenge}/join', [ChallengeController::class, 'join'])->name('challenges.join');
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress');
    Route::get('/progress/history', [ProgressController::class, 'history'])->name('progress.history');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/trophies', [TrophyController::class, 'index'])->name('trophies');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
});

// Old static-HTML URLs keep working.
Route::permanentRedirect('/index.html', '/');
Route::permanentRedirect('/login.html', '/login');
Route::permanentRedirect('/upload-ride.html', '/upload-ride');
Route::permanentRedirect('/active-challenges.html', '/challenges');
Route::permanentRedirect('/progress.html', '/progress');
Route::permanentRedirect('/my-trophies.html', '/trophies');
Route::permanentRedirect('/notifications.html', '/notifications');
