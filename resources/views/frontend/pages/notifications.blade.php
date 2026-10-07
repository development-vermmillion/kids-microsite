@extends('frontend.layouts.app')

@section('title', 'Notifications')
@section('body_class', 'notifications-page')

@section('content')
    <!-- Notifications Content -->
    <div class="notifications-content">
        <div class="notifications-header">
            <div class="header-icon-wrapper shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">notifications_active</span>
            </div>
            <h2 class="font-headline-lg">Your Alerts</h2>
            <p class="font-body-lg text-variant">See what your friends are up to and your latest achievements!</p>
        </div>

        <div class="notifications-list">
            @forelse ($notifications as $notification)
                <div class="notification-card soft-shadow {{ $notification->is_unread ? 'unread' : '' }}">
                    <div class="icon-circle bg-{{ $notification->color }}-light">
                        <span class="material-symbols-outlined color-{{ $notification->color }}">{{ $notification->icon }}</span>
                    </div>
                    <div class="card-body">
                        <h3 class="font-headline-sm">{{ $notification->title }}</h3>
                        <p class="font-body-md text-variant desc">{{ $notification->message }}</p>
                        <span class="time font-label-sm">{{ $notification->time_label }}</span>
                    </div>
                    @if ($notification->is_unread)
                        <div class="unread-dot"></div>
                    @endif
                </div>
            @empty
                <p class="font-body-md text-variant">No alerts yet. Go for a ride!</p>
            @endforelse
        </div>
    </div>
@endsection
