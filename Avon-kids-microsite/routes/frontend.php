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
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/upload-ride', [RideController::class, 'create'])->name('rides.create');
Route::post('/upload-ride', [RideController::class, 'store'])->name('rides.store')->middleware('throttle:20,1');

Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
Route::post('/challenges/{challenge}/join', [ChallengeController::class, 'join'])->name('challenges.join');

Route::get('/progress', [ProgressController::class, 'index'])->name('progress');
Route::get('/progress/history', [ProgressController::class, 'history'])->name('progress.history');

Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
Route::get('/trophies', [TrophyController::class, 'index'])->name('trophies');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

// Old static-HTML URLs keep working.
Route::permanentRedirect('/index.html', '/');
Route::permanentRedirect('/login.html', '/login');
Route::permanentRedirect('/upload-ride.html', '/upload-ride');
Route::permanentRedirect('/active-challenges.html', '/challenges');
Route::permanentRedirect('/progress.html', '/progress');
Route::permanentRedirect('/my-trophies.html', '/trophies');
Route::permanentRedirect('/notifications.html', '/notifications');
