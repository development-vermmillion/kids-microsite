<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\RiderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = RiderNotification::with('rider')
            ->when($request->query('rider'), fn ($q, $id) => $q->where('rider_id', $id))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('backend.notifications.index', compact('notifications'));
    }

    public function create(Request $request): View
    {
        return view('backend.notifications.form', [
            'riders' => Rider::orderBy('name')->get()->mapWithKeys(fn ($r) => [$r->id => "{$r->name} ({$r->mobile})"])->all(),
            'selectedRider' => $request->query('rider'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'audience' => ['required', Rule::in(['all', 'one'])],
            'rider_id' => ['nullable', 'required_if:audience,one', 'exists:riders,id'],
            'title' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'color' => ['required', Rule::in(['primary', 'secondary', 'tertiary', 'neutral'])],
        ], [
            'rider_id.required_if' => 'Choose which rider should get this alert.',
        ]);

        $riderIds = $data['audience'] === 'all'
            ? Rider::where('is_active', true)->pluck('id')
            : collect([$data['rider_id']]);

        $now = now();
        DB::transaction(function () use ($riderIds, $data, $now) {
            foreach ($riderIds->chunk(500) as $chunk) {
                RiderNotification::insert($chunk->map(fn ($id) => [
                    'rider_id' => $id,
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'icon' => $data['icon'],
                    'color' => $data['color'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            }
        });

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Alert sent to '.$riderIds->count().' '.str('rider')->plural($riderIds->count()).'.');
    }

    public function destroy(RiderNotification $notification): RedirectResponse
    {
        $notification->delete();

        return back()->with('success', 'Alert deleted.');
    }
}
