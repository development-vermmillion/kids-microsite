@extends('frontend.layouts.app')

@use('App\Support\Format')

@section('title', 'Dashboard')

@section('content')
    <!-- Hero Banner Section -->
    <section class="hero-banner-section soft-shadow">
        <img alt="Hero Banner" class="hero-banner-image" src="{{ asset('frontend/images/new-hero-bg.png') }}" />

        <div class="hero-actions">
            <a href="{{ route('login') }}" class="hero-upload-btn font-headline-sm">
                Login Now
                <span class="material-symbols-outlined">login</span>
            </a>
            <a href="{{ route('rides.create') }}" class="hero-upload-btn font-headline-sm hero-secondary-btn">
                Upload Ride
                <span class="material-symbols-outlined">upload_file</span>
            </a>
        </div>
    </section>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <div class="dashboard-grid">

            <!-- Leaderboard & Progress Section -->
            <section class="leaderboard-section">
                <h3 class="section-title font-headline-md">Leaderboard / <span class="red-heading">Progress</span></h3>
                <div class="leaderboard-grid">
                    <!-- Hall of Fame -->
                    <div class="hall-of-fame">
                        <h4 class="sub-title font-headline-sm">
                            <span class="material-symbols-outlined icon">emoji_events</span>
                            Hall of Fame
                        </h4>
                        <div class="leaderboard-card">
                            @if ($champion = $leaderboard->first())
                                <div class="top-player">
                                    <div class="player-info">
                                        <span class="rank font-headline-sm">1</span>
                                        @if ($champion->avatar_url)
                                            <img alt="Champion" src="{{ $champion->avatar_url }}" />
                                        @else
                                            <div class="avatar-placeholder bg-secondary font-label-lg">{{ $champion->initials }}</div>
                                        @endif
                                        <div class="details">
                                            <p class="name font-headline-sm">{{ $champion->name }}</p>
                                            <p class="badge font-label-sm">
                                                <span class="material-symbols-outlined icon">military_tech</span>
                                                Champion
                                            </p>
                                        </div>
                                    </div>
                                    <span class="score font-headline-sm">{{ Format::number($champion->total_km) }} Km</span>
                                </div>
                            @endif
                            <div class="other-players">
                                @php($placeholderColors = ['bg-secondary', 'bg-tertiary', 'bg-gray', 'bg-gray'])
                                @foreach ($leaderboard->slice(1)->values() as $i => $player)
                                    <div class="player-row">
                                        <div class="player-info">
                                            <span class="rank font-headline-sm">{{ $i + 2 }}</span>
                                            <div class="avatar-placeholder {{ $placeholderColors[$i] ?? 'bg-gray' }} font-label-lg">
                                                {{ $player->initials }}</div>
                                            <p class="name font-label-lg">{{ $player->name }}</p>
                                        </div>
                                        <span class="score font-label-lg">{{ Format::number($player->total_km) }} Km</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Your Progress -->
                    <div class="your-progress">
                        <h4 class="sub-title font-headline-sm">
                            <span class="material-symbols-outlined icon">trending_up</span>
                            Your Progress
                        </h4>
                        <div class="progress-card">
                            @if ($nextBadge)
                                <div class="next-badge">
                                    <div class="header">
                                        <p class="title font-headline-sm">Next Badge: {{ $nextBadge->name }}</p>
                                        <span class="percentage font-headline-sm">{{ $nextBadge->pivot->progress_percent }}%</span>
                                    </div>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: {{ $nextBadge->pivot->progress_percent }}%;">
                                            <span class="material-symbols-outlined icon">local_fire_department</span>
                                            <div class="glass"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="stats-grid">
                                <div class="stat-box primary">
                                    <p class="label font-label-sm">Total Rides</p>
                                    <p class="value font-headline-lg">{{ $totalRides }}</p>
                                </div>
                                <div class="stat-box secondary">
                                    <p class="label font-label-sm">Milestones</p>
                                    <p class="value font-headline-lg">{{ $milestonesCount }}</p>
                                </div>
                            </div>
                            <div class="recent-milestones">
                                <p class="label font-label-sm">Recent Milestones</p>
                                <div class="milestone-icons">
                                    @foreach ($recentMilestones as $milestone)
                                        <div class="milestone-icon {{ $milestone->color }}" title="{{ $milestone->name }}">
                                            <span class="material-symbols-outlined icon">{{ $milestone->icon }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Hero Section -->
            <section class="hero-section girl-background-banner">
                <div class="girl-background-box">
                    <div class="hero-content ">
                        <h2 class="font-headline-lg">Ready for next <span class="highlight">Adventure?</span></h2>
                        <p class="font-body-lg">The Weekend Warrior challenge just started! Ride 5 miles this weekend to
                            unlock an exclusive badge.</p>
                        <button class="cta-btn chunky-shadow font-headline-sm"
                            onclick="window.location.href='{{ route('challenges.index') }}'">
                            View Challenge <span class="material-symbols-outlined">rocket_launch</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <div class="stats-section">
                <!-- Community Miles -->
                <div class="community-miles chunky-shadow-primary">
                    <span class="material-symbols-outlined icon">public</span>
                    <h3 class="font-label-lg">Community Miles</h3>
                    <div class="counter-value font-headline-lg">
                        <span id="counter" data-target="{{ $communityMiles }}">{{ number_format($communityMiles) }}</span>
                    </div>
                    <p class="font-label-sm">Miles ridden by Kids Avon today!</p>
                </div>

                <!-- Global Challenge Progress -->
                <div class="global-challenge soft-shadow">
                    <div class="challenge-header">
                        <div class="title-area">
                            <h3 class="font-headline-sm"><span class="material-symbols-outlined icon">explore</span>
                                Next Badge</h3>
                        </div>
                        <span class="percentage font-headline-sm">{{ $communityPercent }}%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-fill" style="width: {{ $communityPercent }}%;">
                            <span class="material-symbols-outlined icon">star</span>
                            <div class="glass-overlay"></div>
                        </div>
                    </div>
                    <div class="challenge-labels font-body-md font-label-sm">
                        <span>0m</span>
                        <span class="goal">{{ Format::number($communityGoal) }} Km Goal</span>
                    </div>
                </div>
            </div>

            <!-- Badges Section -->
            <section class="badges-section soft-shadow">
                <div class="decorative-bg">
                    <span class="material-symbols-outlined icon-star-1">stars</span>
                    <span class="material-symbols-outlined icon-star-2">auto_awesome</span>
                    <span class="material-symbols-outlined icon-star-3">workspace_premium</span>
                </div>

                <div class="badges-header">
                    <div class="title-area">
                        <div class="indicator"></div>
                        <h3 class="font-headline-md">New Badges <span class="red-heading">to Earn</span></h3>
                    </div>
                    <a class="view-all-btn font-label-lg chunky-shadow-primary" href="{{ route('challenges.index') }}">
                        View All <span class="material-symbols-outlined icon">arrow_forward</span>
                    </a>
                </div>

                <div class="badges-grid">
                    @php($lockedIndex = 0)
                    @foreach ($homeBadges as $badge)
                        @if ($riderBadges->get($badge->id)?->pivot->unlocked_at)
                            <!-- Unlocked Badge -->
                            <div class="badge-card unlocked">
                                <div class="hover-bg"></div>
                                <div class="image-container">
                                    <div class="glow"></div>
                                    <img alt="{{ $badge->name }}" src="{{ $badge->image_url }}" />
                                </div>
                                <h4 class="font-headline-sm">{{ $badge->name }}</h4>
                                <p class="font-body-md">{{ $badge->description }}</p>
                                <div class="status-badge font-label-sm">
                                    <span class="material-symbols-outlined icon">lock_open</span>
                                    Unlocked
                                </div>
                            </div>
                        @else
                            <!-- Locked Badge -->
                            <div class="badge-card locked hover-{{ $badge->color }}">
                                <div class="image-container {{ $lockedIndex++ % 2 === 0 ? 'pos-right' : 'pos-center' }}">
                                    <img alt="{{ $badge->name }}" src="{{ $badge->image_url }}" />
                                    <div class="lock-overlay">
                                        <div class="lock-icon-container">
                                            <span class="material-symbols-outlined icon">lock</span>
                                        </div>
                                    </div>
                                </div>
                                <h4 class="font-headline-sm">{{ $badge->name }}</h4>
                                <p class="font-body-md">{{ $badge->description }}</p>
                                <div class="status-badge font-label-sm">Locked</div>
                            </div>
                        @endif
                    @endforeach

                    <!-- Mystery Badge -->
                    <div class="badge-card mystery">
                        <div class="pulse-bg"></div>
                        <div class="mystery-icon-container">
                            <span class="material-symbols-outlined icon">help</span>
                        </div>
                        <h4 class="font-headline-sm">Mystery Badge</h4>
                        <p class="font-body-md font-label-sm">Keep riding to reveal!</p>
                    </div>
                </div>
            </section>

            <!-- How it Works Section -->
            <section class="how-it-works-section soft-shadow">
                <img alt="How it works" class="how-it-works-image" src="{{ asset('frontend/images/how-it-works.jpeg') }}" />
            </section>

            <!-- Verification Section -->
            <section class="verification-section ">
                <div class="decorative-blob blob-1"></div>
                <div class="decorative-blob blob-2"></div>

                <div class="verification-content">
                    <div class="example-area">
                        <div class="info-area">
                            <div class="header">
                                <div class="icon-container">
                                    <span class="material-symbols-outlined icon">verified</span>
                                </div>
                                <h3 class="font-headline-md section-title-left">
                                    Ride <span class="highlight">Verification</span>
                                </h3>
                            </div>
                            <p class="font-body-lg description">
                                Ready to claim your badges? Upload your ride screenshot from Strava.
                                Make sure your <strong>activity date</strong> and <strong>distance</strong>
                                (at least 1 mile!) are clearly visible.
                            </p>

                            <div class="upload-action">
                                <a href="{{ route('rides.create') }}" class="upload-btn font-headline-sm chunky-shadow">
                                    Upload Ride
                                    <span class="material-symbols-outlined">upload_file</span>
                                </a>
                                <span class="hint font-label-sm">Supports JPG, PNG</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Safety Info Section -->
            <section class="safety-section soft-shadow">
                <img alt="Safety Banner" class="safety-banner-image" src="{{ asset('frontend/images/ride-safe-banner.png') }}" />
            </section>

            <!-- FAQ Section -->
            <section class="faq-section soft-shadow">
                <h3 class="section-title font-headline-md">FAQ / Suggested <span class="red-heading">Questions</span></h3>
                <div class="faq-grid">
                    @foreach ($faqs as $faq)
                        <div class="faq-item">
                            <h4 class="font-headline-sm color-{{ $faq->color }}">
                                <span class="material-symbols-outlined icon">{{ $faq->icon }}</span>
                                {{ $faq->question }}
                            </h4>
                            <p class="font-body-md">{{ $faq->answer }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Community miles counter animation
        document.addEventListener('DOMContentLoaded', () => {
            const counterElement = document.getElementById('counter');
            if (!counterElement) return;

            const end = parseInt(counterElement.dataset.target, 10) || 0;
            let start = Math.max(0, end - 450);
            const duration = 2000;
            const stepTime = Math.max(1, Math.floor(duration / Math.max(1, end - start)));

            const timer = setInterval(() => {
                start += 5;
                if (start > end) start = end;
                counterElement.textContent = start.toLocaleString();
                if (start === end) {
                    clearInterval(timer);
                }
            }, stepTime);
        });
    </script>
@endpush
