<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/frontend.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/backend.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Admin panel: logged-out admins go to the admin login,
        // logged-in admins visiting the login page go to the dashboard.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        $middleware->alias([
            // Website pages that need a logged-in rider (email + OTP).
            'rider' => \App\Http\Middleware\RequireRider::class,
            // Invisible trap field + "sent too fast" check on forms.
            'bot-guard' => \App\Http\Middleware\GuardAgainstBots::class,
            // Cloudflare Turnstile robot check.
            'turnstile' => \App\Http\Middleware\RequireTurnstile::class,
        ]);

        // Bot traps run before the rate limits, so turned-away bots don't use up
        // a real visitor's tries.
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\ThrottleRequests::class,
            prepend: \App\Http\Middleware\GuardAgainstBots::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Never send these back to the browser when a form has an error.
        $exceptions->dontFlash(['password', 'password_confirmation', 'current_password', 'otp', 'cf-turnstile-response', 'kv_website', 'kv_ts']);
    })->create();
