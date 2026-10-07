@extends('frontend.layouts.app')

@section('title', 'My Trophies')
@section('body_class', 'trophies-page')

@section('content')
    <!-- Trophies Content -->
    <div class="trophies-content">
        <div class="trophies-header">
            <div class="header-icon-wrapper shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
            </div>
            <h2 class="font-headline-lg">Trophy Room</h2>
            <p class="font-body-lg text-variant">Look at all the amazing badges you've collected on your cycling journey!</p>
        </div>

        <div class="trophies-grid">
            @foreach ($trophies as $trophy)
                @if ($trophy->unlocked_at)
                    <div class="trophy-card soft-shadow unlocked">
                        <div class="trophy-icon-wrapper bg-{{ $trophy->color }}-gradient shadow-glow">
                            <span class="material-symbols-outlined trophy-icon"
                                style="font-variation-settings: 'FILL' 1;">{{ $trophy->icon }}</span>
                        </div>
                        <h3 class="font-headline-sm">{{ $trophy->name }}</h3>
                        <p class="font-body-md text-variant">{{ $trophy->description }}</p>
                        <span class="date font-label-sm">Unlocked: {{ $trophy->unlocked_at->format('M j, Y') }}</span>
                    </div>
                @else
                    <div class="trophy-card soft-shadow locked">
                        <div class="trophy-icon-wrapper locked-bg">
                            <span class="material-symbols-outlined trophy-icon">{{ $trophy->icon }}</span>
                        </div>
                        <h3 class="font-headline-sm">{{ $trophy->name }}</h3>
                        <p class="font-body-md text-variant">{{ $trophy->description }}</p>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill fill-neutral" style="width: {{ $trophy->progress_percent }}%;"></div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection
