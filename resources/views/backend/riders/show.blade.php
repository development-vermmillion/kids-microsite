@extends('backend.layouts.app')

@use('App\Support\Format')

@section('title', $rider->name)

@section('content')
    <div class="page-head">
        <div class="who" style="gap:16px">
            <x-admin.avatar :rider="$rider" size="lg" />
            <div>
                <p><a href="{{ route('admin.riders.index') }}">← All riders</a></p>
                <h1>{{ $rider->name }}
                    @unless ($rider->is_active)
                        <span class="pill pill-off">Hidden</span>
                    @endunless
                </h1>
                <p>{{ $rider->mobile }} · {{ $rider->level_title }} · joined {{ $rider->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.rides.create', ['rider' => $rider->id]) }}" class="btn btn-light">
                <span class="material-symbols-outlined">add</span> Add ride
            </a>
            <a href="{{ route('admin.notifications.create', ['rider' => $rider->id]) }}" class="btn btn-light">
                <span class="material-symbols-outlined">send</span> Send alert
            </a>
            <a href="{{ route('admin.riders.edit', $rider) }}" class="btn btn-primary">
                <span class="material-symbols-outlined">edit</span> Edit
            </a>
        </div>
    </div>

    <div class="stat-grid">
        <div class="card stat">
            <div class="icon tone-red"><span class="material-symbols-outlined">route</span></div>
            <div><div class="label">Verified distance</div><div class="value">{{ Format::km($stats['km']) }} km</div></div>
        </div>
        <div class="card stat">
            <div class="icon tone-green"><span class="material-symbols-outlined">directions_bike</span></div>
            <div><div class="label">Verified rides</div><div class="value">{{ $stats['verified'] }}</div></div>
        </div>
        <div class="card stat">
            <div class="icon tone-amber"><span class="material-symbols-outlined">hourglass_empty</span></div>
            <div><div class="label">Pending review</div><div class="value">{{ $stats['pending'] }}</div></div>
        </div>
        <div class="card stat">
            <div class="icon tone-blue"><span class="material-symbols-outlined">timer</span></div>
            <div><div class="label">Time in saddle</div><div class="value">{{ round($stats['minutes'] / 60) }} hrs</div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Rides</h2>
            <a href="{{ route('admin.rides.index', ['q' => $rider->mobile]) }}">All of this rider's rides</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ride</th>
                        <th>Date</th>
                        <th class="num">Distance</th>
                        <th class="num">Duration</th>
                        <th>Status</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rides as $ride)
                        <tr>
                            <td>{{ $ride->display_title }}</td>
                            <td>{{ $ride->ride_date->format('d M Y') }}</td>
                            <td class="num">{{ Format::km($ride->distance_km) }} km</td>
                            <td class="num muted">{{ $ride->duration_label ?? '—' }}</td>
                            <td><x-admin.status :status="$ride->status" :label="$ride->status === 'rejected' ? $ride->status_label : null" /></td>
                            <td class="actions"><a href="{{ route('admin.rides.show', $ride) }}" class="btn btn-light btn-sm">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No rides yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid-2" style="margin-top:20px; align-items:start">
        {{-- Badges --}}
        <form method="POST" action="{{ route('admin.riders.badges.update', $rider) }}" class="card">
            @csrf
            @method('PUT')
            <div class="card-head">
                <h2>Badges & trophies</h2>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Badge</th>
                            <th>Unlocked</th>
                            <th>Unlocked on</th>
                            <th class="num">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($badges as $badge)
                            @php($pivot = $riderBadges->get($badge->id)?->pivot)
                            <tr>
                                <td>
                                    <div class="who">
                                        <span class="icon-chip {{ $badge->color }}"><span class="material-symbols-outlined">{{ $badge->icon }}</span></span>
                                        {{ $badge->name }}
                                    </div>
                                </td>
                                <td>
                                    <input type="checkbox" name="badges[{{ $badge->id }}][unlocked]" value="1"
                                        style="width:18px;height:18px;accent-color:var(--red)" @checked($pivot?->unlocked_at)
                                        onchange="const r = this.closest('tr'); r.querySelector('.unlock-date').hidden = !this.checked; r.querySelectorAll('.progress-cell').forEach(el => el.hidden = this.checked)" />
                                </td>
                                <td>
                                    <input type="date" class="input input-sm unlock-date" style="width:140px"
                                        name="badges[{{ $badge->id }}][unlocked_at]" @if (! $pivot?->unlocked_at) hidden @endif
                                        value="{{ $pivot?->unlocked_at ? \Illuminate\Support\Carbon::parse($pivot->unlocked_at)->toDateString() : '' }}" />
                                </td>
                                <td class="num">
                                    @if ($badge->is_auto)
                                        <span class="pill tone-blue" title="Calculated from verified rides">Auto · {{ $pivot->progress_percent ?? 0 }}%</span>
                                        <input type="hidden" name="badges[{{ $badge->id }}][progress]" value="{{ $pivot->progress_percent ?? 0 }}" />
                                    @else
                                        <span class="progress-cell" @if ($pivot?->unlocked_at) hidden @endif>
                                            <input type="number" min="0" max="100" class="input input-sm" style="width:64px"
                                                name="badges[{{ $badge->id }}][progress]" value="{{ $pivot->progress_percent ?? 0 }}" /> %
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <span class="muted" style="margin-right:auto;font-size:13px">Ticking a new badge sends the rider an alert. “Auto” progress comes from verified rides.</span>
                <button type="submit" class="btn btn-primary">Save badges</button>
            </div>
        </form>

        {{-- Challenges --}}
        <form method="POST" action="{{ route('admin.riders.challenges.update', $rider) }}" class="card">
            @csrf
            @method('PUT')
            <div class="card-head">
                <h2>Challenges</h2>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Challenge</th>
                            <th>Joined</th>
                            <th class="num">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($challenges as $challenge)
                            @php($pivot = $riderChallenges->get($challenge->id)?->pivot)
                            <tr>
                                <td>
                                    {{ $challenge->title }}
                                    @if ($pivot?->completed_at)
                                        <span class="pill pill-verified">Done</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="checkbox" name="challenges[{{ $challenge->id }}][joined]" value="1"
                                        style="width:18px;height:18px;accent-color:var(--red)" @checked($pivot) />
                                </td>
                                <td class="num" style="white-space:nowrap">
                                    @if ($challenge->is_auto)
                                        <span class="pill tone-blue" title="Calculated from verified rides">Auto</span>
                                        <strong>{{ Format::number($pivot->progress_value ?? 0) }}</strong>
                                    @else
                                        <input type="number" min="0" step="0.1" class="input input-sm" style="width:72px"
                                            name="challenges[{{ $challenge->id }}][progress]" value="{{ Format::number($pivot->progress_value ?? 0) }}" />
                                    @endif
                                    <span class="muted">/ {{ Format::number($challenge->target_value) }} {{ $challenge->unit }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">No challenges yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save challenges</button>
            </div>
        </form>
    </div>

    <div class="card card-pad" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap">
        <div>
            <strong>Delete rider</strong>
            <div class="muted" style="font-size:14px">Removes the rider with all their rides, badges, challenges and alerts.</div>
        </div>
        <x-admin.delete :action="route('admin.riders.destroy', $rider)" label="Delete rider" class="btn btn-danger"
            confirm="Delete {{ $rider->name }} and ALL their rides? This cannot be undone." />
    </div>
@endsection
