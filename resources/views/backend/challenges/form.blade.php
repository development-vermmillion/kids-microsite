@extends('backend.layouts.app')

@section('title', $challenge->exists ? 'Edit challenge' : 'Add challenge')

@section('content')
    <div class="page-head">
        <div>
            <p><a href="{{ route('admin.challenges.index') }}">← Challenges</a></p>
            <h1>{{ $challenge->exists ? 'Edit challenge' : 'Add a challenge' }}</h1>
        </div>
    </div>

    <form method="POST" class="card"
        action="{{ $challenge->exists ? route('admin.challenges.update', $challenge) : route('admin.challenges.store') }}">
        @csrf
        @if ($challenge->exists)
            @method('PUT')
        @endif

        <div class="card-pad form-grid">
            <div class="form-section"><span class="material-symbols-outlined">edit_note</span> What riders see</div>
            <x-admin.input name="title" label="Title" :value="$challenge->title" required full placeholder="e.g. 10km Weekly Milestone" />
            <x-admin.textarea name="description" label="Description" :value="$challenge->description" required
                hint="One or two sentences, shown on the challenge card." />
            <div class="form-section"><span class="material-symbols-outlined">trending_up</span> Goal & progress</div>
            <x-admin.select name="metric" label="How progress is counted" :options="\App\Support\ProgressService::METRICS"
                :value="$challenge->metric" full
                hint="Automatic options update from the rider's verified rides between the start and end dates (or from the day they joined). “Set by admin” means you enter progress on each rider's page." />
            <x-admin.input name="target_value" type="number" step="0.1" min="0" label="Goal" :value="$challenge->target_value" required
                hint="The number a rider must reach, e.g. 10." />
            <x-admin.input name="unit" label="Unit (optional)" :value="$challenge->unit"
                hint="Shown after the numbers, e.g. “km” → 6.5 / 10 km. Leave empty for counts." />
            <x-admin.input name="progress_label" label="Progress label" :value="$challenge->progress_label" required
                hint="Text above the progress bar, e.g. Parks Visited." />
            <div class="form-section"><span class="material-symbols-outlined">redeem</span> Reward</div>
            <x-admin.input name="reward_points" type="number" min="0" label="Reward points" :value="$challenge->reward_points ?? 0" required />
            <x-admin.select name="badge_id" label="Badge unlocked on completion (optional)" :options="$badges"
                :value="$challenge->badge_id" placeholder="No badge" />
            <div class="form-section"><span class="material-symbols-outlined">palette</span> Look</div>
            <x-admin.icon :value="$challenge->icon" :color="$challenge->color" />
            <x-admin.color :value="$challenge->color" hint="Card header, progress bar and button colour." />
            <div class="form-section"><span class="material-symbols-outlined">event</span> Schedule & visibility <small>Riders only see live challenges between these dates</small></div>
            <x-admin.input name="starts_at" type="date" label="Starts (optional)" :value="$challenge->starts_at?->toDateString()" />
            <x-admin.input name="ends_at" type="date" label="Ends (optional)" :value="$challenge->ends_at?->toDateString()" />
            <x-admin.input name="sort_order" type="number" min="0" label="Display order" :value="$challenge->sort_order" required
                hint="Lower numbers show first." />
            <x-admin.checkbox name="is_active" label="Live on the website" :checked="$challenge->is_active" />
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.challenges.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $challenge->exists ? 'Save changes' : 'Add challenge' }}</button>
        </div>
    </form>
@endsection
