@extends('backend.layouts.app')

@use('App\Support\Format')

@section('title', 'Ride review')

@section('content')
    <div class="page-head">
        <div>
            <h1>Ride review</h1>
            <p>Check uploaded rides and verify or reject them. Only verified rides count on the leaderboard.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.rides.create') }}" class="btn btn-light">
                <span class="material-symbols-outlined">add</span> Add ride
            </a>
        </div>
    </div>

    <div class="card">
        <form method="GET" class="filters">
            <div class="tabs">
                @foreach (['' => 'All', 'pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'] as $key => $label)
                    <a href="{{ route('admin.rides.index', array_filter(['status' => $key, 'q' => $search])) }}"
                        class="tab {{ (string) $status === $key ? 'active' : '' }}">
                        {{ $label }}
                        <span class="count">{{ $key === '' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}" />
            @endif
            <input type="search" name="q" value="{{ $search }}" class="input" placeholder="Search rider, mobile or ride"
                style="margin-left:auto" />
            <button class="btn btn-light btn-sm" type="submit"><span class="material-symbols-outlined">search</span></button>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rider</th>
                        <th>Ride</th>
                        <th>Ride date</th>
                        <th class="num">Distance</th>
                        <th class="num">Duration</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rides as $ride)
                        <tr>
                            <td>
                                <div class="who">
                                    <x-admin.avatar :rider="$ride->rider" />
                                    <div>
                                        <a href="{{ route('admin.riders.show', $ride->rider) }}">{{ $ride->rider->name }}</a>
                                        <div class="muted" style="font-size:12px">{{ $ride->rider->mobile }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="{{ $ride->title ? '' : 'muted' }}">{{ $ride->display_title }}</td>
                            <td>{{ $ride->ride_date->format('d M Y') }}</td>
                            <td class="num">{{ Format::km($ride->distance_km) }} km</td>
                            <td class="num muted">{{ $ride->duration_label ?? '—' }}</td>
                            <td>
                                @if ($ride->proof_url)
                                    <img src="{{ $ride->proof_url }}" alt="Proof" class="thumb" />
                                @else
                                    <span class="muted">None</span>
                                @endif
                            </td>
                            <td>
                                <x-admin.status :status="$ride->status" />
                                @if ($ride->status === 'rejected' && $ride->rejection_reason)
                                    <div class="muted" style="font-size:12px;margin-top:3px">{{ $ride->rejection_reason }}</div>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.rides.show', $ride) }}" class="btn btn-sm {{ $ride->status === 'pending' ? 'btn-primary' : 'btn-light' }}">
                                    {{ $ride->status === 'pending' ? 'Review' : 'Open' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">No rides found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rides->links() }}
    </div>
@endsection
