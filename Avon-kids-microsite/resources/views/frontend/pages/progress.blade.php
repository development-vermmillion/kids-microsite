@extends('frontend.layouts.app')

@use('App\Support\Format')

@section('title', 'My Progress')
@section('body_class', 'progress-page')

@section('content')
    <!-- Progress Dashboard Content -->
    <div class="progress-content">

        <div class="progress-header">
            <h2 class="font-headline-lg">My Progress Dashboard</h2>
            <p class="font-body-lg text-variant">Keep pedaling! Here is a summary of all your rides.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card soft-shadow">
                <div class="icon-wrapper bg-primary">
                    <span class="material-symbols-outlined">route</span>
                </div>
                <div class="stat-info">
                    <p class="stat-label font-label-sm text-variant">Total Distance</p>
                    <p class="stat-value font-headline-md">{{ Format::km($totalDistance) }} <span class="unit font-body-md">km</span></p>
                </div>
            </div>

            <div class="stat-card soft-shadow">
                <div class="icon-wrapper bg-secondary">
                    <span class="material-symbols-outlined">directions_bike</span>
                </div>
                <div class="stat-info">
                    <p class="stat-label font-label-sm text-variant">Total Rides</p>
                    <p class="stat-value font-headline-md">{{ $totalRides }}</p>
                </div>
            </div>

            <div class="stat-card soft-shadow">
                <div class="icon-wrapper bg-tertiary">
                    <span class="material-symbols-outlined">military_tech</span>
                </div>
                <div class="stat-info">
                    <p class="stat-label font-label-sm text-variant">Badges Earned</p>
                    <p class="stat-value font-headline-md">{{ $badgesEarned }}</p>
                </div>
            </div>

            <div class="stat-card soft-shadow">
                <div class="icon-wrapper bg-neutral">
                    <span class="material-symbols-outlined">timer</span>
                </div>
                <div class="stat-info">
                    <p class="stat-label font-label-sm text-variant">Time in Saddle</p>
                    <p class="stat-value font-headline-md">{{ $hoursInSaddle }} <span class="unit font-body-md">hrs</span></p>
                </div>
            </div>
        </div>

        <!-- Recent Rides List -->
        <div class="recent-rides-section soft-shadow">
            <div class="section-top">
                <h3 class="font-headline-md">Recent Rides</h3>
                <a href="{{ route('rides.create') }}" class="add-ride-btn font-label-lg">
                    <span class="material-symbols-outlined">add</span> Log a Ride
                </a>
            </div>

            <div class="rides-list">
                @forelse ($recentRides as $ride)
                    <div class="ride-item">
                        <div class="ride-icon">
                            <span class="material-symbols-outlined">pedal_bike</span>
                        </div>
                        <div class="ride-details">
                            <h4 class="font-headline-sm">{{ $ride->title ?: 'Ride' }}</h4>
                            <p class="font-body-md text-variant">
                                {{ $ride->ride_date->format('d M Y') }} • {{ Format::km($ride->distance_km) }} km{{ $ride->duration_label ? ' • '.$ride->duration_label : '' }}
                            </p>
                        </div>
                        <div class="ride-status status-{{ $ride->status }} font-label-sm">
                            <span class="material-symbols-outlined">{{ $ride->status_icon }}</span>
                            {{ $ride->status_label }}
                        </div>
                    </div>
                @empty
                    <p class="font-body-md text-variant">No rides yet. Upload your first ride to get started!</p>
                @endforelse
            </div>
            <div class="view-all">
                <a href="#" class="font-label-lg">View All History</a>
            </div>
        </div>

    </div>
@endsection
