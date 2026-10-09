<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Ride;
use App\Models\Rider;
use App\Support\OtpService;
use App\Support\Turnstile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('backend.dashboard', [
            'setupWarnings' => $this->setupWarnings(),
            'stats' => [
                'riders' => Rider::count(),
                'pending' => Ride::where('status', Ride::STATUS_PENDING)->count(),
                'verifiedKm' => (float) Ride::verified()->sum('distance_km'),
                'activeChallenges' => Challenge::where('is_active', true)->count(),
                'ridesThisWeek' => Ride::where('created_at', '>=', now()->startOfWeek())->count(),
                'newRidersThisWeek' => Rider::where('created_at', '>=', now()->startOfWeek())->count(),
            ],
            'pendingRides' => Ride::with('rider')
                ->where('status', Ride::STATUS_PENDING)
                ->oldest()
                ->take(6)
                ->get(),
            'topRiders' => Rider::withSum(['rides as total_km' => fn ($q) => $q->verified()], 'distance_km')
                ->orderByDesc('total_km')
                ->take(5)
                ->get(),
            'recentRides' => Ride::with('rider')
                ->whereIn('status', [Ride::STATUS_VERIFIED, Ride::STATUS_REJECTED])
                ->latest('reviewed_at')
                ->latest('id')
                ->take(5)
                ->get(),
        ]);
    }

    /** Settings in .env that still need changing before the site goes live. */
    private function setupWarnings(): array
    {
        $mailer = config('mail.default');

        return array_values(array_filter([
            ! Turnstile::enabled()
                ? 'The Cloudflare robot check is switched off. Add TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY to the .env file.' : null,
            Turnstile::usingTestKeys()
                ? 'The Cloudflare robot check is using testing keys, which let everyone through. Replace TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY in .env with your own keys.' : null,
            OtpService::testCode()
                ? 'OTP testing mode is on: no emails are sent and every code is '.OtpService::testCode().'. Empty OTP_TEST_CODE in .env before launch.' : null,
            ! OtpService::testCode() && in_array($mailer, ['log', 'array'], true)
                ? 'OTP emails are only written to the log file, not sent. Set MAIL_MAILER=smtp and the SMTP details in .env.' : null,
            str_ends_with((string) config('mail.from.address'), '@example.com')
                ? 'The "from" address of OTP emails is still '.config('mail.from.address').'. Set MAIL_FROM_ADDRESS in .env.' : null,
        ]));
    }
}
