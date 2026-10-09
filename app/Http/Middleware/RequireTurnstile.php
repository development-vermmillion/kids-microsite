<?php

namespace App\Http\Middleware;

use App\Support\Turnstile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Form posts on these routes must pass the Cloudflare Turnstile robot check.
 * Usage: ->middleware('turnstile:login') – the allowed widget action(s).
 */
class RequireTurnstile
{
    public function handle(Request $request, Closure $next, string ...$actions): Response
    {
        if (! $request->isMethod('GET') && ($error = Turnstile::check($request->input('cf-turnstile-response'), $request->ip(), $actions))) {
            throw ValidationException::withMessages(['cf-turnstile-response' => $error]);
        }

        return $next($request);
    }
}
