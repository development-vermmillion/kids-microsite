@extends('backend.layouts.app')

@use('App\Support\Format')

@section('title', 'Riders')

@section('content')
    <div class="page-head">
        <div>
            <h1>Riders</h1>
            <p>Kids registered on the site. Click a rider to see their rides, badges and challenges.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.riders.create') }}" class="btn btn-primary">
                <span class="material-symbols-outlined">person_add</span> Add rider
            </a>
        </div>
    </div>

    <div class="card">
        <form method="GET" class="filters">
            <input type="search" name="q" value="{{ $search }}" class="input" placeholder="Search name, username, email or mobile" />
            <select name="sort" class="input" onchange="this.form.submit()">
                <option value="km" @selected($sort === 'km')>Sort: most km</option>
                <option value="name" @selected($sort === 'name')>Sort: name A–Z</option>
                <option value="newest" @selected($sort === 'newest')>Sort: newest</option>
            </select>
            <button class="btn btn-light btn-sm" type="submit"><span class="material-symbols-outlined">search</span></button>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rider</th>
                        <th>Contact</th>
                        <th class="num">Level</th>
                        <th class="num">Verified km</th>
                        <th class="num">Rides</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riders as $rider)
                        <tr>
                            <td>
                                <div class="who">
                                    <x-admin.avatar :rider="$rider" />
                                    <a href="{{ route('admin.riders.show', $rider) }}">{{ $rider->name }}</a>
                                </div>
                            </td>
                            <td>
                                @if ($rider->email)
                                    <div>{{ $rider->email }}
                                        @if ($rider->email_verified_at)
                                            <span class="material-symbols-outlined verified-tick" title="Email verified">verified</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="muted" style="font-size:12px">{{ $rider->username ? '@'.$rider->username.' · ' : '' }}{{ $rider->mobile }}</div>
                            </td>
                            <td class="num">{{ $rider->level }}</td>
                            <td class="num"><strong>{{ Format::number($rider->total_km ?? 0) }}</strong></td>
                            <td class="num">
                                {{ $rider->rides_count }}
                                @if ($rider->pending_count)
                                    <span class="pill pill-pending" title="Waiting for review">{{ $rider->pending_count }} pending</span>
                                @endif
                            </td>
                            <td>
                                @if ($rider->is_active)
                                    <span class="pill pill-verified">Active</span>
                                @else
                                    <span class="pill pill-off">Hidden</span>
                                @endif
                            </td>
                            <td class="muted">{{ $rider->created_at->format('d M Y') }}</td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.riders.show', $rider) }}" class="btn btn-light btn-sm">Open</a>
                                    <a href="{{ route('admin.riders.edit', $rider) }}" class="btn btn-light btn-sm">
                                        <span class="material-symbols-outlined">edit</span></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">No riders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $riders->links() }}
    </div>
@endsection
