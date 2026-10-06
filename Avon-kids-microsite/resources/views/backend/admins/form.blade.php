@extends('backend.layouts.app')

@section('title', $admin->exists ? 'Edit admin' : 'Add admin')

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ route('admin.admins.index') }}">← Admin users</a></p>
            <h1>{{ $admin->exists ? 'Edit '.$admin->name : 'Add an admin' }}</h1>
        </div>
    </div>

    <form method="POST" class="card" action="{{ $admin->exists ? route('admin.admins.update', $admin) : route('admin.admins.store') }}">
        @csrf
        @if ($admin->exists)
            @method('PUT')
        @endif

        <div class="card-pad form-grid">
            <x-admin.input name="name" label="Name" :value="$admin->name" required />
            <x-admin.input name="email" type="email" label="Email (used to sign in)" :value="$admin->email" required autocomplete="off" />
            <x-admin.input name="password" type="password" :label="$admin->exists ? 'New password' : 'Password'"
                :hint="$admin->exists ? 'Leave empty to keep the current password.' : 'At least 8 characters.'"
                autocomplete="new-password" :required="! $admin->exists" />
            <x-admin.input name="password_confirmation" type="password" label="Repeat password" autocomplete="new-password"
                :required="! $admin->exists" />
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.admins.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $admin->exists ? 'Save changes' : 'Add admin' }}</button>
        </div>
    </form>
@endsection
