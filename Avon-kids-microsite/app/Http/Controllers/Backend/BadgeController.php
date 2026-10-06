<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::withCount(['riders as unlocked_count' => fn ($q) => $q->whereNotNull('badge_rider.unlocked_at')])
            ->orderBy('sort_order')
            ->get();

        return view('backend.badges.index', compact('badges'));
    }

    public function create(): View
    {
        return view('backend.badges.form', ['badge' => new Badge([
            'icon' => 'military_tech',
            'color' => 'primary',
            'show_in_trophy_room' => true,
            'sort_order' => (Badge::max('sort_order') ?? 0) + 1,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $request->hasFile('image') ? Uploads::store($request->file('image'), 'badges') : null;

        Badge::create($data);

        return redirect()->route('admin.badges.index')->with('success', 'Badge added.');
    }

    public function edit(Badge $badge): View
    {
        return view('backend.badges.form', compact('badge'));
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            Uploads::delete($badge->image);
            $data['image'] = $request->hasFile('image') ? Uploads::store($request->file('image'), 'badges') : null;
        }

        $badge->update($data);

        return redirect()->route('admin.badges.index')->with('success', 'Badge updated.');
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        Uploads::delete($badge->image);
        $badge->delete();

        return redirect()->route('admin.badges.index')->with('success', 'Badge deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'color' => ['required', Rule::in(['primary', 'secondary', 'tertiary'])],
            'show_on_home' => ['boolean'],
            'show_in_trophy_room' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
        ], [
            'icon.regex' => 'Use the icon name in lowercase with underscores, e.g. military_tech.',
        ]);
    }
}
