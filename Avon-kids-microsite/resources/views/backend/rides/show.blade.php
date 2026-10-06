@extends('backend.layouts.app')

@use('App\Support\Format')

@section('title', 'Ride #'.$ride->id)

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ route('admin.rides.index') }}">← All rides</a></p>
            <h1>{{ $ride->title ?: 'Ride' }} <x-admin.status :status="$ride->status" /></h1>
            <p>Uploaded {{ $ride->created_at->format('d M Y, g:i a') }} by {{ $ride->rider->name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.rides.edit', $ride) }}" class="btn btn-light">
                <span class="material-symbols-outlined">edit</span> Edit
            </a>
            <x-admin.delete :action="route('admin.rides.destroy', $ride)" confirm="Delete this ride and its proof image?" class="btn btn-danger" />
        </div>
    </div>

    <div class="split">
        <div class="card card-pad">
            <h2 style="font-size:19px;margin-bottom:14px">Photo proof</h2>
            @if ($ride->proof_url)
                <a href="{{ $ride->proof_url }}" target="_blank" rel="noopener">
                    <img src="{{ $ride->proof_url }}" alt="Ride proof" class="proof-image" />
                </a>
                <p class="muted" style="margin:8px 0 0;font-size:13px">Click the image to open it full size.</p>
            @else
                <div class="proof-empty">
                    <span class="material-symbols-outlined" style="font-size:40px">image_not_supported</span>
                    No screenshot was uploaded with this ride.
                </div>
            @endif
        </div>

        <div>
            <div class="card card-pad">
                <h2 style="font-size:19px;margin-bottom:14px">Ride details</h2>
                <dl class="kv">
                    <dt>Rider</dt>
                    <dd><a href="{{ route('admin.riders.show', $ride->rider) }}">{{ $ride->rider->name }}</a></dd>
                    <dt>Mobile</dt>
                    <dd>{{ $ride->rider->mobile }}</dd>
                    <dt>Ride date</dt>
                    <dd>{{ $ride->ride_date->format('l, d M Y') }}</dd>
                    <dt>Ride time</dt>
                    <dd>{{ $ride->ride_time ? \Illuminate\Support\Carbon::parse($ride->ride_time)->format('g:i a') : '—' }}</dd>
                    <dt>Distance</dt>
                    <dd>{{ Format::km($ride->distance_km) }} km
                        @if ($ride->distance_km < 1.6)
                            <span class="pill pill-pending" title="The site asks for at least 1 mile (1.6 km)">Under 1 mile</span>
                        @endif
                    </dd>
                    <dt>Duration</dt>
                    <dd>{{ $ride->duration_label ?? '—' }}</dd>
                    @if ($ride->reviewed_at)
                        <dt>Reviewed</dt>
                        <dd>{{ $ride->reviewed_at->format('d M Y, g:i a') }}</dd>
                    @endif
                    @if ($ride->status === 'rejected')
                        <dt>Reason</dt>
                        <dd>{{ $ride->rejection_reason }}</dd>
                    @endif
                    <dt>Rider's totals</dt>
                    <dd>{{ Format::number($riderTotals['verifiedKm']) }} km verified · {{ $riderTotals['rides'] }} rides</dd>
                </dl>
            </div>

            <div class="card card-pad">
                <h2 style="font-size:19px;margin-bottom:6px">Decision</h2>
                <p class="muted" style="margin:0 0 14px;font-size:14px">The rider gets a notification on the site either way.</p>

                @if ($ride->status !== 'verified')
                    <form method="POST" action="{{ route('admin.rides.verify', $ride) }}" style="margin-bottom:12px">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success" style="width:100%">
                            <span class="material-symbols-outlined">check_circle</span> Verify ride
                        </button>
                    </form>
                @endif

                @if ($ride->status !== 'rejected')
                    <details class="reject" @if ($errors->has('rejection_reason')) open @endif>
                        <summary class="btn btn-danger" style="width:100%">
                            <span class="material-symbols-outlined">cancel</span> Reject ride…
                        </summary>
                        <form method="POST" action="{{ route('admin.rides.reject', $ride) }}">
                            @csrf
                            @method('PATCH')
                            <div class="field @error('rejection_reason') has-error @enderror">
                                <label for="rejection_reason">Reason shown to the rider</label>
                                <input id="rejection_reason" name="rejection_reason" class="input" list="reject-reasons"
                                    value="{{ old('rejection_reason') }}" placeholder="e.g. Date not visible" required />
                                <datalist id="reject-reasons">
                                    @foreach ($rejectReasons as $reason)
                                        <option value="{{ $reason }}"></option>
                                    @endforeach
                                </datalist>
                                @error('rejection_reason')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px">Confirm rejection</button>
                        </form>
                    </details>
                @endif

                @if ($nextPending)
                    <p style="margin:16px 0 0;text-align:center">
                        <a href="{{ route('admin.rides.show', $nextPending) }}">Skip to next pending ride →</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
