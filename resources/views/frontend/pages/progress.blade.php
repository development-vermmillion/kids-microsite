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
                    @if ($pendingDistance > 0)
                        <p class="stat-pending">+{{ Format::km($pendingDistance) }} km waiting for review</p>
                    @endif
                </div>
            </div>

            <div class="stat-card soft-shadow">
                <div class="icon-wrapper bg-secondary">
                    <span class="material-symbols-outlined">directions_bike</span>
                </div>
                <div class="stat-info">
                    <p class="stat-label font-label-sm text-variant">Total Rides</p>
                    <p class="stat-value font-headline-md">{{ $totalRides }}</p>
                    @if ($pendingRides > 0)
                        <p class="stat-pending">+{{ $pendingRides }} waiting for review</p>
                    @endif
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

        <!-- My Challenges -->
        <div class="recent-rides-section soft-shadow my-challenges">
            <div class="section-top">
                <h3 class="font-headline-md">My Challenges</h3>
                <a href="{{ route('challenges.index') }}" class="add-ride-btn font-label-lg">
                    <span class="material-symbols-outlined">flag</span> All Challenges
                </a>
            </div>

            @forelse ($challenges as $challenge)
                <div class="my-challenge">
                    <div class="my-challenge-icon bg-{{ $challenge->color }}-soft">
                        <span class="material-symbols-outlined">{{ $challenge->icon }}</span>
                    </div>
                    <div class="my-challenge-body">
                        <div class="my-challenge-top">
                            <h4 class="font-headline-sm">{{ $challenge->title }}</h4>
                            @if ($challenge->is_completed)
                                <span class="ride-status status-verified font-label-sm">
                                    <span class="material-symbols-outlined">check_circle</span> Completed
                                </span>
                            @else
                                <span class="my-challenge-count">
                                    {{ Format::number($challenge->progress) }} / {{ Format::number($challenge->target_value) }}{{ $challenge->unit ? ' '.$challenge->unit : '' }}
                                </span>
                            @endif
                        </div>
                        <div class="progress-split my-challenge-bar">
                            <div class="progress-bar-fill fill-{{ $challenge->color }}" style="width: {{ $challenge->percent }}%;"></div>
                            @if ($challenge->pending_percent)
                                <div class="progress-pending pending-{{ $challenge->color }}" style="width: {{ $challenge->pending_percent }}%;"></div>
                            @endif
                        </div>
                        @if ($challenge->pending > 0)
                            <p class="pending-note">
                                <span class="material-symbols-outlined">hourglass_top</span>
                                +{{ Format::number($challenge->pending) }}{{ $challenge->unit ? ' '.$challenge->unit : '' }} waiting for review
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="font-body-md text-variant my-challenge-empty">
                    You haven't joined a challenge yet.
                    <a href="{{ route('challenges.index') }}">Pick one to start!</a>
                </p>
            @endforelse
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
                            <h4 class="font-headline-sm">{{ $ride->display_title }}</h4>
                            <p class="font-body-md text-variant">
                                {{ $ride->ride_date->format('d M Y') }} • {{ Format::km($ride->distance_km) }} km{{ $ride->duration_label ? ' • '.$ride->duration_label : '' }}
                            </p>
                        </div>
                        <div class="ride-status status-{{ $ride->status }} font-label-sm" title="{{ $ride->status_label }}">
                            <span class="material-symbols-outlined">{{ $ride->status_icon }}</span>
                            {{ $ride->status_label }}
                        </div>
                    </div>
                @empty
                    <p class="font-body-md text-variant">No rides yet. <a href="{{ route('rides.create') }}">Upload your first ride</a> to get started!</p>
                @endforelse
            </div>
            @if ($totalUploaded > $recentRides->count())
                <div class="view-all">
                    <a href="{{ route('progress.history') }}" class="font-label-lg">View All History ({{ $totalUploaded }} rides)</a>
                </div>
            @elseif ($totalUploaded)
                <div class="view-all">
                    <a href="{{ route('progress.history') }}" class="font-label-lg">View All History</a>
                </div>
            @endif
        </div>

    </div>
@endsection
