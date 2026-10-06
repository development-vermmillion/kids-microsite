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
            @foreach ($challenges as $challenge)
                <div class="challenge-card soft-shadow {{ $challenge->is_joined ? '' : 'unstarted' }}">
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

                        <div class="progress-section">
                            <div class="progress-info">
                                <span class="font-label-sm">{{ $challenge->progress_label }}</span>
                                <span class="font-label-sm">
                                    {{ Format::number($challenge->progress) }} / {{ Format::number($challenge->target_value) }}{{ $challenge->unit ? ' '.$challenge->unit : '' }}
                                </span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill fill-{{ $challenge->color }}" style="width: {{ $challenge->percent }}%;"></div>
                            </div>
                        </div>

                        <button class="btn btn-{{ $challenge->color }} chunky-shadow font-label-lg">
                            {{ $challenge->is_joined ? 'Log Progress' : 'Accept Challenge' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
