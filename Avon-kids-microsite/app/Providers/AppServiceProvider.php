<?php

namespace App\Providers;

use App\Models\Ride;
use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use App\Support\CurrentRider;
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
    }
}
