<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('rider_id');

        return redirect()->route('login');
    }
}
