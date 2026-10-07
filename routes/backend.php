<?php

use App\Http\Controllers\Backend\AdminUserController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\BadgeController;
use App\Http\Controllers\Backend\ChallengeController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\FaqController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\RideController;
use App\Http\Controllers\Backend\RiderController;
use App\Http\Controllers\Backend\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend (admin panel) routes
|--------------------------------------------------------------------------
|
| Loaded from bootstrap/app.php with the "web" middleware, the "/admin"
| URL prefix and the "admin." route-name prefix.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:admin-login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rides: review queue + full management
    Route::patch('rides/{ride}/verify', [RideController::class, 'verify'])->name('rides.verify');
    Route::patch('rides/{ride}/reject', [RideController::class, 'reject'])->name('rides.reject');
    Route::resource('rides', RideController::class);

    // Riders, with their badge and challenge progress
    Route::put('riders/{rider}/badges', [RiderController::class, 'updateBadges'])->name('riders.badges.update');
    Route::put('riders/{rider}/challenges', [RiderController::class, 'updateChallenges'])->name('riders.challenges.update');
    Route::resource('riders', RiderController::class);

    Route::resource('challenges', ChallengeController::class)->except('show');
    Route::resource('badges', BadgeController::class)->except('show');
    Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('faqs', FaqController::class)->except('show');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::resource('admins', AdminUserController::class)->except('show')->parameters(['admins' => 'admin']);
});
