<?php

namespace App\Http\Middleware;

use App\Support\CurrentRider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pages that need a logged-in rider send guests to the email OTP login, then back. */
class RequireRider
{
    public function __construct(private CurrentRider $current) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->current->check()) {
            // After logging in, come back here (for a form post: the page it was sent from).
            session()->put('url.intended', $request->isMethod('GET') ? $request->fullUrl() : url()->previous());

            return redirect()->route('login')
                ->with('info', 'Please log in to continue.');
        }

        return $next($request);
    }
}
