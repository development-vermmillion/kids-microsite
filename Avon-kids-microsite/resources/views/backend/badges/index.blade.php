@extends('backend.layouts.app')

@section('title', 'Badges & trophies')

@section('content')
    <div class="page-head">
        <div>
            <h1>Badges & trophies</h1>
            <p>Badges appear in the home page “New Badges to Earn” section and/or the Trophy Room. Unlock them for riders from each rider's page.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.badges.create') }}" class="btn btn-primary">
                <span class="material-symbols-outlined">add</span> Add badge
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="num">#</th>
                        <th>Badge</th>
                        <th>Image</th>
                        <th>Shown on</th>
                        <th class="num">Unlocked by</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($badges as $badge)
                        <tr>
                            <td class="num muted">{{ $badge->sort_order }}</td>
                            <td>
                                <div class="who">
                                    <span class="icon-chip {{ $badge->color }}"><span class="material-symbols-outlined">{{ $badge->icon }}</span></span>
                                    <div>
                                        <strong>{{ $badge->name }}</strong>
                                        <div class="muted" style="font-size:13px">{{ $badge->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($badge->image_url)
                                    <img src="{{ $badge->image_url }}" alt="" class="thumb" />
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($badge->show_on_home)
                                    <span class="pill tone-red">Home page</span>
                                @endif
                                @if ($badge->show_in_trophy_room)
                                    <span class="pill tone-blue">Trophy Room</span>
                                @endif
                                @if (! $badge->show_on_home && ! $badge->show_in_trophy_room)
                                    <span class="pill pill-off">Hidden</span>
                                @endif
                            </td>
                            <td class="num">{{ $badge->unlocked_count }} {{ str('rider')->plural($badge->unlocked_count) }}</td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.badges.edit', $badge) }}" class="btn btn-light btn-sm">
                                        <span class="material-symbols-outlined">edit</span> Edit</a>
                                    <x-admin.delete :action="route('admin.badges.destroy', $badge)"
                                        confirm="Delete this badge? It is removed from every rider who earned it." label="" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No badges yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
