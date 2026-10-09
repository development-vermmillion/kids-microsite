<?php

namespace App\Http\Middleware;

use App\Support\Turnstile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/** Form posts on these routes must pass the Cloudflare Turnstile robot check. */
class RequireTurnstile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ($error = Turnstile::check($request->input('cf-turnstile-response'), $request->ip()))) {
            throw ValidationException::withMessages(['cf-turnstile-response' => $error]);
        }

        return $next($request);
    }
}
