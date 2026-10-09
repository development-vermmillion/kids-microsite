<?php

namespace App\Providers;

use App\Models\Ride;
use App\Models\Setting;
use App\Support\CurrentRider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CurrentRider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Requests that come through Cloudflare carry the visitor's real IP (config/kidsavon.php).
        \Illuminate\Http\Middleware\TrustProxies::at(config('kidsavon.trusted_proxies'));

        // Every frontend view gets the current rider (header) and support email (footer).
        View::composer('frontend.*', function ($view) {
            $view->with('currentRider', app(CurrentRider::class)->get());
            $view->with('supportEmail', Setting::get('support_email', 'avon@avoncycles.com'));
        });

        // Admin sidebar shows how many rides are waiting for review.
        View::composer('backend.layouts.app', function ($view) {
            $view->with('pendingRidesCount', Ride::where('status', Ride::STATUS_PENDING)->count());
        });

        Paginator::defaultView('backend.partials.pagination');

        // "Send OTP": 3 emails per minute per address (plus the 60-second wait in
        // OtpService), and a generous cap per connection (schools and families
        // often share one internet connection).
        RateLimiter::for('otp', fn (Request $request) => [
            Limit::perMinute(3)->by('otp-email:'.\App\Support\OtpService::normaliseEmail($request->input('email'))),
            Limit::perHour(20)->by('otp-email-hour:'.\App\Support\OtpService::normaliseEmail($request->input('email'))),
            Limit::perMinute(30)->by('otp-ip:'.$request->ip()),
        ]);

        // Each action gets its own counter, so a busy website can never lock
        // the admin out (plain throttle:N,1 limits share one counter per IP).
        RateLimiter::for('rider-login', fn (Request $request) => [
            Limit::perMinute(10)->by('rider-login-email:'.\App\Support\OtpService::normaliseEmail($request->input('email'))),
            Limit::perMinute(60)->by('rider-login-ip:'.$request->ip()),
        ]);
        RateLimiter::for('ride-upload', fn (Request $request) => Limit::perMinute(20)->by('ride-upload:'.(session('rider_id') ?: $request->ip())));
        RateLimiter::for('admin-login', fn (Request $request) => [
            Limit::perMinute(5)->by('admin-login-email:'.strtolower((string) $request->input('email'))),
            Limit::perMinute(20)->by('admin-login-ip:'.$request->ip()),
        ]);
    }
}
