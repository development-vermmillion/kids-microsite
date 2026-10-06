@extends('backend.layouts.app')

@use('App\Support\Format')

@section('title', 'Challenges')

@section('content')
    <div class="page-head">
        <div>
            <h1>Challenges</h1>
            <p>Shown on the Active Challenges page. Riders see their own progress on each one.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.challenges.create') }}" class="btn btn-primary">
                <span class="material-symbols-outlined">add</span> Add challenge
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="num">#</th>
                        <th>Challenge</th>
                        <th>Goal</th>
                        <th class="num">Reward</th>
                        <th>Dates</th>
                        <th class="num">Joined</th>
                        <th>Status</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($challenges as $challenge)
                        <tr>
                            <td class="num muted">{{ $challenge->sort_order }}</td>
                            <td>
                                <div class="who">
                                    <span class="icon-chip {{ $challenge->color }}"><span class="material-symbols-outlined">{{ $challenge->icon }}</span></span>
                                    <div>
                                        <strong>{{ $challenge->title }}</strong>
                                        <div class="muted" style="font-size:13px;max-width:360px">{{ $challenge->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ Format::number($challenge->target_value) }} {{ $challenge->unit ?: '' }}
                                <div class="muted" style="font-size:12px">{{ $challenge->progress_label }}</div></td>
                            <td class="num">{{ number_format($challenge->reward_points) }} pts</td>
                            <td class="muted" style="font-size:13px">
                                @if ($challenge->starts_at || $challenge->ends_at)
                                    {{ $challenge->starts_at?->format('d M') ?? '…' }} – {{ $challenge->ends_at?->format('d M Y') ?? '…' }}
                                @else
                                    Always on
                                @endif
                            </td>
                            <td class="num">{{ $challenge->riders_count }}
                                @if ($challenge->completed_count)
                                    <div class="muted" style="font-size:12px">{{ $challenge->completed_count }} done</div>
                                @endif
                            </td>
                            <td>
                                @if ($challenge->is_active)
                                    <span class="pill pill-verified">Live</span>
                                @else
                                    <span class="pill pill-off">Hidden</span>
                                @endif
                            </td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.challenges.edit', $challenge) }}" class="btn btn-light btn-sm">
                                        <span class="material-symbols-outlined">edit</span> Edit</a>
                                    <x-admin.delete :action="route('admin.challenges.destroy', $challenge)"
                                        confirm="Delete this challenge? Riders' progress on it is deleted too." label="" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty">No challenges yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
