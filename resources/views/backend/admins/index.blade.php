@extends('backend.layouts.app')

@section('title', 'Admin users')

@section('content')
    <div class="page-head">
        <div>
            <h1>Admin users</h1>
            <p>People who can sign in to this admin panel.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                <span class="material-symbols-outlined">person_add</span> Add admin
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Added</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admins as $admin)
                        <tr>
                            <td><strong>{{ $admin->name }}</strong>
                                @if ($admin->is(auth()->user()))
                                    <span class="pill tone-blue">You</span>
                                @endif
                            </td>
                            <td>{{ $admin->email }}</td>
                            <td class="muted">{{ $admin->created_at->format('d M Y') }}</td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-light btn-sm">
                                        <span class="material-symbols-outlined">edit</span> Edit</a>
                                    @unless ($admin->is(auth()->user()))
                                        <x-admin.delete :action="route('admin.admins.destroy', $admin)" confirm="Remove this admin?" label="" />
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
