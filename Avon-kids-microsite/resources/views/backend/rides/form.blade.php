@extends('backend.layouts.app')

@section('title', $ride->exists ? 'Edit ride' : 'Add ride')

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ $ride->exists ? route('admin.rides.show', $ride) : route('admin.rides.index') }}">← Back</a></p>
            <h1>{{ $ride->exists ? 'Edit ride' : 'Add a ride' }}</h1>
            @unless ($ride->exists)
                <p>Log a ride on behalf of a rider, for example one sent in by email.</p>
            @endunless
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data"
        action="{{ $ride->exists ? route('admin.rides.update', $ride) : route('admin.rides.store') }}" class="card">
        @csrf
        @if ($ride->exists)
            @method('PUT')
        @endif

        <div class="card-pad form-grid">
            <x-admin.select name="rider_id" label="Rider" :options="$riders" :value="$ride->rider_id"
                placeholder="Choose a rider" required full />
            <x-admin.input name="title" label="Ride name (optional)" :value="$ride->title" placeholder="e.g. Weekend Trail Explorer" full />
            <x-admin.input name="ride_date" type="date" label="Ride date" :value="$ride->ride_date?->toDateString()"
                max="{{ now()->toDateString() }}" required />
            <x-admin.input name="ride_time" type="time" label="Ride time" :value="$ride->ride_time ? substr($ride->ride_time, 0, 5) : null" />
            <x-admin.input name="distance_km" type="number" step="0.1" min="0" label="Distance (km)" :value="$ride->distance_km" required />
            <x-admin.input name="duration_minutes" type="number" min="0" label="Duration (minutes)" :value="$ride->duration_minutes"
                hint="Used for Time in Saddle on the progress page." />
            <x-admin.select name="status" label="Status" :value="$ride->status"
                :options="['pending' => 'Pending review', 'verified' => 'Verified', 'rejected' => 'Rejected']" />
            <x-admin.input name="rejection_reason" label="Rejection reason" :value="$ride->rejection_reason"
                hint="Only needed when the status is Rejected." />
            <x-admin.image name="proof_image" label="Photo proof" :current="$ride->proof_url" full />
        </div>

        <div class="form-actions">
            <a href="{{ $ride->exists ? route('admin.rides.show', $ride) : route('admin.rides.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $ride->exists ? 'Save changes' : 'Add ride' }}</button>
        </div>
    </form>
@endsection
