@extends('backend.layouts.app')

@section('title', 'Send alert')

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ route('admin.notifications.index') }}">← Notifications</a></p>
            <h1>Send an alert</h1>
            <p>For example a new challenge, an event, or a message to one rider.</p>
        </div>
    </div>

    @php($audience = old('audience', $selectedRider ? 'one' : 'all'))

    <form method="POST" action="{{ route('admin.notifications.store') }}" class="card">
        @csrf
        <div class="card-pad form-grid">
            <div class="field full">
                <span class="label">Send to</span>
                <div style="display:flex;gap:20px;flex-wrap:wrap">
                    <label class="check"><input type="radio" name="audience" value="all" @checked($audience === 'all')
                            onchange="document.getElementById('rider-pick').style.display='none'" /> All active riders</label>
                    <label class="check"><input type="radio" name="audience" value="one" @checked($audience === 'one')
                            onchange="document.getElementById('rider-pick').style.display=''" /> One rider</label>
                </div>
            </div>
            <div id="rider-pick" class="full" style="{{ $audience === 'one' ? '' : 'display:none' }}">
                <x-admin.select name="rider_id" label="Rider" :options="$riders" :value="$selectedRider" placeholder="Choose a rider" />
            </div>
            <x-admin.input name="title" label="Title" required full placeholder="e.g. New Challenge Available" />
            <x-admin.textarea name="message" label="Message" required
                placeholder="e.g. The Summer Sprint challenge is now open. Join to earn double points!" />
            <x-admin.icon value="campaign" />
            <x-admin.color value="primary" :neutral="true" />
        </div>
        <div class="form-actions">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined">send</span> Send alert</button>
        </div>
    </form>
@endsection
