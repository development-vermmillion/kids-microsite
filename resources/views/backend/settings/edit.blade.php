@extends('backend.layouts.app')

@section('title', 'Site settings')

@section('content')
    <div class="page-head">
        <div>
            <h1>Site settings</h1>
            <p>Numbers and contact details shown across the website.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="card">
        @csrf
        @method('PUT')

        <div class="card-head"><h2>Home page – community stats</h2></div>
        <div class="card-pad form-grid">
            <x-admin.input name="community_miles_today" type="number" min="0" label="Community Miles counter"
                :value="$values['community_miles_today']" required
                hint="The big red “Community Miles … ridden by Kids Avon today” number." />
            <div></div>
            <x-admin.input name="community_goal_km" type="number" step="0.1" min="0" label="Community goal (km)"
                :value="$values['community_goal_km']" required hint="Shown as “150 Km Goal” under the progress bar." />
            <x-admin.input name="community_progress_km" type="number" step="0.1" min="0" label="Community progress (km)"
                :value="$values['community_progress_km']" required
                hint="How far the community has got. The site shows it as a percentage of the goal." />
        </div>

        <div class="card-head" style="border-top:1px solid var(--line)"><h2>Contact</h2></div>
        <div class="card-pad form-grid">
            <x-admin.input name="support_email" type="email" label="Help & Support email" :value="$values['support_email']" required
                hint="Shown in the footer of every page." />
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save settings</button>
        </div>
    </form>
@endsection
