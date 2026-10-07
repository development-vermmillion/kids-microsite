@extends('frontend.layouts.app')

@use('App\Support\Format')

@section('title', 'Active Challenges')
@section('body_class', 'challenges-page')

@section('content')
    <!-- Challenges Content -->
    <div class="challenges-content">
        <div class="challenges-header">
            <div class="header-icon-wrapper shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">flag</span>
            </div>
            <h2 class="font-headline-lg">Active Challenges</h2>
            <p class="font-body-lg text-variant">Join challenges to earn points, unlock exclusive badges, and level up!</p>
        </div>

        <div class="challenges-grid">
            @forelse ($challenges as $challenge)
                <div class="challenge-card soft-shadow {{ $challenge->is_joined ? '' : 'unstarted' }} {{ $challenge->is_completed ? 'completed' : '' }}">
                    <div class="card-top bg-{{ $challenge->color }}-gradient">
                        <span class="material-symbols-outlined challenge-icon">{{ $challenge->icon }}</span>
                        <div class="badge-reward">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">stars</span>
                            <span class="font-label-sm">{{ number_format($challenge->reward_points) }} pts</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3 class="font-headline-sm">{{ $challenge->title }}</h3>
                        <p class="font-body-md text-variant desc">{{ $challenge->description }}</p>
                        @if ($challenge->ends_at)
                            <p class="challenge-dates">
                                <span class="material-symbols-outlined">event</span>
                                @if ($challenge->ends_at->isToday())
                                    Ends today!
                                @else
                                    @php($daysLeft = (int) now()->startOfDay()->diffInDays($challenge->ends_at))
                                    Ends {{ $challenge->ends_at->format('j M') }} · {{ $daysLeft }} {{ str('day')->plural($daysLeft) }} left
                                @endif
                            </p>
                        @endif

                        <div class="progress-section">
                            <div class="progress-info">
                                <span class="font-label-sm">{{ $challenge->progress_label }}</span>
                                <span class="font-label-sm">
                                    {{ Format::number($challenge->progress) }} / {{ Format::number($challenge->target_value) }}{{ $challenge->unit ? ' '.$challenge->unit : '' }}
                                </span>
                            </div>
                            <div class="progress-bar-bg progress-split">
                                <div class="progress-bar-fill fill-{{ $challenge->color }}" style="width: {{ $challenge->percent }}%;"></div>
                                @if ($challenge->pending_percent)
                                    <div class="progress-pending pending-{{ $challenge->color }}" style="width: {{ $challenge->pending_percent }}%;"
                                        title="Waiting for review"></div>
                                @endif
                            </div>
                            @if ($challenge->pending > 0)
                                <p class="pending-note">
                                    <span class="material-symbols-outlined">hourglass_top</span>
                                    +{{ Format::number($challenge->pending) }}{{ $challenge->unit ? ' '.$challenge->unit : '' }} waiting for review
                                </p>
                            @endif
                        </div>

                        @if ($challenge->is_completed)
                            <span class="btn btn-done font-label-lg">
                                <span class="material-symbols-outlined">verified</span> Completed
                            </span>
                        @elseif ($challenge->is_joined)
                            <a href="{{ route('rides.create') }}" class="btn btn-{{ $challenge->color }} chunky-shadow font-label-lg">
                                Log Progress
                            </a>
                        @elseif (! $currentRider)
                            <a href="{{ route('login', ['redirect' => '/challenges']) }}" class="btn btn-{{ $challenge->color }} chunky-shadow font-label-lg">
                                Log in to Join
                            </a>
                        @else
                            <form method="POST" action="{{ route('challenges.join', $challenge) }}">
                                @csrf
                                <button type="submit" class="btn btn-{{ $challenge->color }} chunky-shadow font-label-lg">
                                    Accept Challenge
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="challenges-empty">No challenges are running right now. Check back soon!</p>
            @endforelse
        </div>
    </div>
@endsection
