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
        $notifications = $rider ? $rider->notifications()->take(30)->get() : collect();

        // Show them as unread this time, then mark them as seen (the admin panel shows "Seen").
        if ($rider) {
            $rider->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        }

        return view('frontend.pages.notifications', compact('notifications'));
    }
}
