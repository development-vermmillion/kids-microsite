<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Settings editable from the admin panel, with their defaults. */
    public const FIELDS = [
        'community_miles_today' => 0,
        'community_goal_km' => 150,
        'community_progress_km' => 0,
        'support_email' => 'avon@avoncycles.com',
    ];

    public function edit(): View
    {
        $values = collect(self::FIELDS)->map(fn ($default, $key) => Setting::get($key, $default));

        return view('backend.settings.edit', compact('values'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'community_miles_today' => ['required', 'integer', 'min:0'],
            'community_goal_km' => ['required', 'numeric', 'gt:0'],
            'community_progress_km' => ['required', 'numeric', 'min:0'],
            'support_email' => ['required', 'email', 'max:120'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved. The website shows the new values now.');
    }
}
