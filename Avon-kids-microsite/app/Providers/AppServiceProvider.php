<?php

namespace App\Providers;

use App\Models\Setting;
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
    }
}
