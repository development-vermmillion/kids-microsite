@extends('backend.layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-head">
        <div>
            <h1>Notifications</h1>
            <p>Alerts riders see on their notifications page. Ride reviews and new badges create alerts automatically.</p>
        </div>
        <div class="page-actions">
            @if (request('rider'))
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-light">Show all</a>
            @endif
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                <span class="material-symbols-outlined">send</span> Send alert
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Alert</th>
                        <th>Rider</th>
                        <th>Seen</th>
                        <th>Sent</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr>
                            <td>
                                <div class="who">
                                    <span class="icon-chip {{ $notification->color }}"><span class="material-symbols-outlined">{{ $notification->icon }}</span></span>
                                    <div>
                                        <strong>{{ $notification->title }}</strong>
                                        <div class="muted" style="font-size:13px;max-width:460px">{{ $notification->message }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><a href="{{ route('admin.notifications.index', ['rider' => $notification->rider_id]) }}">{{ $notification->rider->name }}</a></td>
                            <td>
                                @if ($notification->read_at)
                                    <span class="pill pill-verified">Seen</span>
                                @else
                                    <span class="pill pill-off">Not yet</span>
                                @endif
                            </td>
                            <td class="muted" style="white-space:nowrap">{{ $notification->created_at->diffForHumans() }}</td>
                            <td class="actions">
                                <x-admin.delete :action="route('admin.notifications.destroy', $notification)" confirm="Delete this alert?" label="" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No alerts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $notifications->links() }}
    </div>
@endsection
