@extends('backend.layouts.app')

@section('title', $rider->exists ? 'Edit rider' : 'Add rider')

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ $rider->exists ? route('admin.riders.show', $rider) : route('admin.riders.index') }}">← Back</a></p>
            <h1>{{ $rider->exists ? 'Edit '.$rider->name : 'Add a rider' }}</h1>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="card"
        action="{{ $rider->exists ? route('admin.riders.update', $rider) : route('admin.riders.store') }}">
        @csrf
        @if ($rider->exists)
            @method('PUT')
        @endif

        <div class="card-pad form-grid">
            <x-admin.input name="name" label="Rider name" :value="$rider->name" required />
            <x-admin.input name="mobile" type="tel" label="Mobile number" :value="$rider->mobile" required
                hint="Used to log in with OTP. Must be unique." />
            <x-admin.input name="level" type="number" min="1" label="Level" :value="$rider->level" required
                hint="Shown in the header as “Level 12 Cyclist”." />
            <x-admin.checkbox name="is_active" label="Active (shown on the leaderboard)" :checked="$rider->is_active" />
            <x-admin.image name="avatar" label="Profile photo" :current="$rider->avatar_url" hint="Square image works best. Up to 2 MB." full />
        </div>

        <div class="form-actions">
            <a href="{{ $rider->exists ? route('admin.riders.show', $rider) : route('admin.riders.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $rider->exists ? 'Save changes' : 'Add rider' }}</button>
        </div>
    </form>
@endsection
