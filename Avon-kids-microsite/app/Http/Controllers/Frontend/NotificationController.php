<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\CurrentRider;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(CurrentRider $current): View
    {
        $rider = $current->get();

        return view('frontend.pages.notifications', [
            'notifications' => $rider ? $rider->notifications()->take(30)->get() : collect(),
        ]);
    }
}
