@extends('frontend.layouts.app')

@use('App\Support\Format')

@section('title', 'Ride History')
@section('body_class', 'progress-page')

@section('content')
    <div class="progress-content">
        <div class="progress-header">
            <h2 class="font-headline-lg">Ride History</h2>
            <p class="font-body-lg text-variant">Every ride you have uploaded, newest first.</p>
        </div>

        <div class="recent-rides-section soft-shadow">
            <div class="section-top">
                <h3 class="font-headline-md">All Rides</h3>
                <a href="{{ route('rides.create') }}" class="add-ride-btn font-label-lg">
                    <span class="material-symbols-outlined">add</span> Log a Ride
                </a>
            </div>

            <nav class="history-filters" aria-label="Filter rides">
                @foreach ([null => 'All', 'verified' => 'Verified', 'pending' => 'Pending', 'rejected' => 'Not approved'] as $key => $label)
                    <a href="{{ route('progress.history', array_filter(['status' => $key])) }}"
                        class="{{ $status === ($key ?: null) ? 'active' : '' }}">
                        {{ $label }} ({{ $key ? ($counts[$key] ?? 0) : $counts->sum() }})
                    </a>
                @endforeach
            </nav>

            <div class="rides-list">
                @forelse ($rides as $ride)
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
                    <p class="font-body-md text-variant">No rides here yet.</p>
                @endforelse
            </div>

            @if ($rides->hasPages())
                <div class="history-pagination">
                    @if ($rides->onFirstPage())
                        <span class="disabled"><span class="material-symbols-outlined">chevron_left</span> Newer</span>
                    @else
                        <a href="{{ $rides->previousPageUrl() }}"><span class="material-symbols-outlined">chevron_left</span> Newer</a>
                    @endif
                    <span>Page {{ $rides->currentPage() }} of {{ $rides->lastPage() }}</span>
                    @if ($rides->hasMorePages())
                        <a href="{{ $rides->nextPageUrl() }}">Older <span class="material-symbols-outlined">chevron_right</span></a>
                    @else
                        <span class="disabled">Older <span class="material-symbols-outlined">chevron_right</span></span>
                    @endif
                </div>
            @endif

            <div class="view-all">
                <a href="{{ route('progress') }}" class="font-label-lg">← Back to My Progress</a>
            </div>
        </div>
    </div>
@endsection
