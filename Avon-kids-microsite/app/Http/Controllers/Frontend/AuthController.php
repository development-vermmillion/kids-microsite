<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Mobile number + OTP login screen.
     * TODO: send OTP, verify it, then store the rider id in session('rider_id').
     */
    public function showLogin(): View
    {
        return view('frontend.pages.login');
    }
}
