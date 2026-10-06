<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RideController extends Controller
{
    /**
     * Upload Ride form.
     * TODO: add a store() action that validates the form, saves the proof image
     * and creates a Ride with status "pending" for admin review.
     */
    public function create(): View
    {
        return view('frontend.pages.upload-ride');
    }
}
