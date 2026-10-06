@extends('backend.layouts.app')

@section('title', $badge->exists ? 'Edit badge' : 'Add badge')

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ route('admin.badges.index') }}">← Badges & trophies</a></p>
            <h1>{{ $badge->exists ? 'Edit badge' : 'Add a badge' }}</h1>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="card"
        action="{{ $badge->exists ? route('admin.badges.update', $badge) : route('admin.badges.store') }}">
        @csrf
        @if ($badge->exists)
            @method('PUT')
        @endif

        <div class="card-pad form-grid">
            <div class="form-section"><span class="material-symbols-outlined">workspace_premium</span> Badge</div>
            <x-admin.input name="name" label="Badge name" :value="$badge->name" required placeholder="e.g. Century Club" />
            <x-admin.input name="description" label="How to earn it" :value="$badge->description" required
                placeholder="e.g. Ride a total of 100km." />
            <div class="form-section"><span class="material-symbols-outlined">palette</span> Look</div>
            <x-admin.icon :value="$badge->icon" :color="$badge->color" label="Icon (Trophy Room & milestones)" />
            <x-admin.color :value="$badge->color" />
            <div class="form-section"><span class="material-symbols-outlined">lock_open</span> Unlocking</div>
            <x-admin.select name="metric" label="How it is unlocked" :options="\App\Support\ProgressService::METRICS"
                :value="$badge->metric"
                hint="Automatic badges unlock by themselves when the rider reaches the goal (all-time verified rides). Badges can also be unlocked by a challenge, or by hand on a rider's page." />
            <x-admin.input name="target_value" type="number" step="0.1" min="0" label="Goal (automatic badges)"
                :value="$badge->target_value" hint="e.g. 100 for “Ride a total of 100km”. Leave empty for “Set by admin”." />
            <div class="form-section"><span class="material-symbols-outlined">visibility</span> Where it appears</div>
            <x-admin.image name="image" label="Badge image (home page)" :current="$badge->image_url"
                hint="Used in the home page “New Badges to Earn” cards. Square image, up to 4 MB." full />
            <x-admin.checkbox name="show_on_home" label="Show on home page (New Badges to Earn)" :checked="$badge->show_on_home" />
            <x-admin.checkbox name="show_in_trophy_room" label="Show in the Trophy Room" :checked="$badge->show_in_trophy_room" />
            <x-admin.input name="sort_order" type="number" min="0" label="Display order" :value="$badge->sort_order" required
                hint="Lower numbers show first." />
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.badges.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $badge->exists ? 'Save changes' : 'Add badge' }}</button>
        </div>
    </form>
@endsection
