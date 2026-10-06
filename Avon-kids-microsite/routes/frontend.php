<?php

use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\ChallengeController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NotificationController;
use App\Http\Controllers\Frontend\ProgressController;
use App\Http\Controllers\Frontend\RideController;
use App\Http\Controllers\Frontend\TrophyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend (rider-facing) routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/upload-ride', [RideController::class, 'create'])->name('rides.create');
Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
Route::get('/progress', [ProgressController::class, 'index'])->name('progress');
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
