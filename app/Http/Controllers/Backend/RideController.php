<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\Rider;
use App\Support\Format;
use App\Support\ProgressService;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RideController extends Controller
{
    /** Quick-pick reasons on the reject form. */
    public const REJECT_REASONS = [
        'Distance too short',
        'Date not visible',
        'Distance not visible',
        'Screenshot unclear',
        'Duplicate ride',
        'Not a bicycle ride',
    ];

    public function __construct(private ProgressService $progress) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));

        $rides = Ride::with('rider')
            ->when(in_array($status, ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('rider', fn ($r) => $r->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"));
            }))
            // Pending rides: oldest first (fair queue). Others: newest first.
            ->when($status === 'pending', fn ($q) => $q->oldest(), fn ($q) => $q->latest('ride_date')->latest('id'))
            ->paginate(20)
            ->withQueryString();

        $counts = Ride::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('backend.rides.index', compact('rides', 'counts', 'status', 'search'));
    }

    public function show(Ride $ride): View
    {
        $ride->load('rider');

        $nextPending = Ride::where('status', Ride::STATUS_PENDING)
            ->where('id', '!=', $ride->id)
            ->oldest()
            ->first();

        return view('backend.rides.show', [
            'ride' => $ride,
            'nextPending' => $nextPending,
            'rejectReasons' => self::REJECT_REASONS,
            'riderTotals' => [
                'verifiedKm' => (float) $ride->rider->rides()->verified()->sum('distance_km'),
                'rides' => $ride->rider->rides()->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('backend.rides.form', [
            'ride' => new Ride([
                'rider_id' => $request->query('rider'),
                'ride_date' => now(),
                'status' => Ride::STATUS_VERIFIED,
            ]),
            'riders' => $this->riderOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['proof_image'] = $request->hasFile('proof_image') ? Uploads::store($request->file('proof_image'), 'rides') : null;
        $data['reviewed_at'] = $data['status'] === Ride::STATUS_PENDING ? null : now();

        $ride = Ride::create($data);
        $this->progress->recalculate($ride->rider);

        return redirect()->route('admin.rides.show', $ride)->with('success', 'Ride added.');
    }

    public function edit(Ride $ride): View
    {
        return view('backend.rides.form', [
            'ride' => $ride,
            'riders' => $this->riderOptions(),
        ]);
    }

    public function update(Request $request, Ride $ride): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('proof_image') || $request->boolean('remove_proof_image')) {
            Uploads::delete($ride->proof_image);
            $data['proof_image'] = $request->hasFile('proof_image') ? Uploads::store($request->file('proof_image'), 'rides') : null;
        }

        if ($data['status'] !== $ride->status) {
            $data['reviewed_at'] = $data['status'] === Ride::STATUS_PENDING ? null : now();
        }

        $oldRider = $ride->rider;
        $ride->update($data);

        // Both riders, in case the ride was moved to someone else.
        $this->progress->recalculate($ride->fresh()->rider);
        if ($oldRider->id !== $ride->rider_id) {
            $this->progress->recalculate($oldRider);
        }

        return redirect()->route('admin.rides.show', $ride)->with('success', 'Ride updated.');
    }

    public function destroy(Ride $ride): RedirectResponse
    {
        Uploads::delete($ride->proof_image);
        $rider = $ride->rider;
        $ride->delete();
        $this->progress->recalculate($rider);

        return redirect()->route('admin.rides.index')->with('success', 'Ride deleted.');
    }

    public function verify(Request $request, Ride $ride): RedirectResponse
    {
        $ride->update([
            'status' => Ride::STATUS_VERIFIED,
            'rejection_reason' => null,
            'reviewed_at' => now(),
        ]);

        if ($request->boolean('notify', true)) {
            $ride->rider->notifications()->create([
                'title' => 'Ride Verified',
                'message' => 'Your '.Format::number($ride->distance_km).' km ride on '.$ride->ride_date->format('d M').' has been verified. Keep pedalling!',
                'icon' => 'pedal_bike',
                'color' => 'tertiary',
            ]);
        }

        return $this->afterReview($ride, 'Ride verified.');
    }

    public function reject(Request $request, Ride $ride): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        $ride->update([
            'status' => Ride::STATUS_REJECTED,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_at' => now(),
        ]);

        if ($request->boolean('notify', true)) {
            $ride->rider->notifications()->create([
                'title' => 'Ride Not Approved',
                'message' => 'Your ride on '.$ride->ride_date->format('d M').' was not approved: '.$data['rejection_reason'].'. You can upload it again.',
                'icon' => 'error',
                'color' => 'neutral',
            ]);
        }

        return $this->afterReview($ride, 'Ride rejected.');
    }

    /** After a review, jump straight to the next ride in the queue if there is one. */
    private function afterReview(Ride $ride, string $message): RedirectResponse
    {
        $this->progress->recalculate($ride->rider);

        $next = Ride::where('status', Ride::STATUS_PENDING)->oldest()->first();

        return $next
            ? redirect()->route('admin.rides.show', $next)->with('success', $message.' Here is the next ride waiting for review.')
            : redirect()->route('admin.rides.show', $ride)->with('success', $message.' The review queue is now empty.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'rider_id' => ['required', 'exists:riders,id'],
            'title' => ['nullable', 'string', 'max:120'],
            'ride_date' => ['required', 'date', 'before_or_equal:today'],
            'ride_time' => ['nullable', 'date_format:H:i'],
            'distance_km' => ['required', 'numeric', 'min:0', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'status' => ['required', Rule::in([Ride::STATUS_PENDING, Ride::STATUS_VERIFIED, Ride::STATUS_REJECTED])],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:255'],
            'proof_image' => ['nullable', 'image', 'max:4096'],
        ], [
            'rejection_reason.required_if' => 'Give a reason when the ride is rejected.',
        ]);
    }

    private function riderOptions(): array
    {
        return Rider::orderBy('name')->get()
            ->mapWithKeys(fn ($r) => [$r->id => "{$r->name} ({$r->mobile})"])
            ->all();
    }
}
