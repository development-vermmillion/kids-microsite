@extends('backend.layouts.app')

@use('App\Support\Format')

@section('title', 'Dashboard')

@section('content')
    @php($hour = now()->hour)
    <div class="welcome">
        <span class="material-symbols-outlined bike">pedal_bike</span>
        <div style="position:relative">
            <h1>{{ $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening') }}, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}!</h1>
            <p>
                @if ($stats['pending'])
                    {{ $stats['pending'] }} {{ str('ride')->plural($stats['pending']) }} {{ $stats['pending'] === 1 ? 'is' : 'are' }} waiting for your review.
                @else
                    All rides are reviewed. Nice work!
                @endif
            </p>
        </div>
        <div class="actions">
            @if ($stats['pending'])
                <a href="{{ route('admin.rides.index', ['status' => 'pending']) }}" class="btn btn-white">
                    <span class="material-symbols-outlined">fact_check</span> Review rides
                </a>
            @endif
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-ghost">
                <span class="material-symbols-outlined">campaign</span> Send alert
            </a>
            <a href="{{ route('admin.challenges.create') }}" class="btn btn-ghost">
                <span class="material-symbols-outlined">add</span> New challenge
            </a>
        </div>
    </div>

    @if ($setupWarnings)
        <div class="card card-pad setup-warnings">
            <h2><span class="material-symbols-outlined">construction</span> Before going live</h2>
            <ul>
                @foreach ($setupWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="stat-grid">
        <div class="card stat">
            <div class="icon tone-amber"><span class="material-symbols-outlined">hourglass_empty</span></div>
            <div>
                <div class="label">Rides waiting for review</div>
                <div class="value">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="card stat">
            <div class="icon tone-red"><span class="material-symbols-outlined">group</span></div>
            <div>
                <div class="label">Riders</div>
                <div class="value">{{ number_format($stats['riders']) }}</div>
                <div class="muted" style="font-size:12px">+{{ $stats['newRidersThisWeek'] }} this week</div>
            </div>
        </div>
        <div class="card stat">
            <div class="icon tone-green"><span class="material-symbols-outlined">route</span></div>
            <div>
                <div class="label">Verified distance</div>
                <div class="value">{{ Format::number($stats['verifiedKm']) }} km</div>
                <div class="muted" style="font-size:12px">{{ $stats['ridesThisWeek'] }} rides uploaded this week</div>
            </div>
        </div>
        <div class="card stat">
            <div class="icon tone-blue"><span class="material-symbols-outlined">flag</span></div>
            <div>
                <div class="label">Active challenges</div>
                <div class="value">{{ $stats['activeChallenges'] }}</div>
            </div>
        </div>
    </div>

    <div class="split">
        <div class="card">
            <div class="card-head">
                <h2>Waiting for review</h2>
                <a href="{{ route('admin.rides.index', ['status' => 'pending']) }}">See all</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rider</th>
                            <th>Ride date</th>
                            <th class="num">Distance</th>
                            <th>Uploaded</th>
                            <th class="actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingRides as $ride)
                            <tr>
                                <td>
                                    <div class="who"><x-admin.avatar :rider="$ride->rider" /> {{ $ride->rider->name }}</div>
                                </td>
                                <td>{{ $ride->ride_date->format('d M Y') }}</td>
                                <td class="num">{{ Format::km($ride->distance_km) }} km</td>
                                <td class="muted">{{ $ride->created_at->diffForHumans() }}</td>
                                <td class="actions">
                                    <a href="{{ route('admin.rides.show', $ride) }}" class="btn btn-light btn-sm">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">All caught up. No rides are waiting for review.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>Hall of Fame</h2>
                <a href="{{ route('admin.riders.index') }}">All riders</a>
            </div>
            <ul class="list-plain">
                @foreach ($topRiders as $i => $rider)
                    <li>
                        <div class="who">
                            <strong style="width:18px">{{ $i + 1 }}</strong>
                            <x-admin.avatar :rider="$rider" />
                            <a href="{{ route('admin.riders.show', $rider) }}">{{ $rider->name }}</a>
                        </div>
                        <strong>{{ Format::number($rider->total_km) }} km</strong>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="card" style="margin-top:20px">
        <div class="card-head">
            <h2>Recently reviewed</h2>
            <a href="{{ route('admin.rides.index') }}">All rides</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rider</th>
                        <th>Ride</th>
                        <th class="num">Distance</th>
                        <th>Status</th>
                        <th>Reviewed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentRides as $ride)
                        <tr>
                            <td>{{ $ride->rider->name }}</td>
                            <td><a href="{{ route('admin.rides.show', $ride) }}">{{ $ride->display_title }}</a>
                                <span class="muted">· {{ $ride->ride_date->format('d M Y') }}</span></td>
                            <td class="num">{{ Format::km($ride->distance_km) }} km</td>
                            <td><x-admin.status :status="$ride->status" /></td>
                            <td class="muted">{{ $ride->reviewed_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">Nothing reviewed yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
