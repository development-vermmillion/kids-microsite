<?php

namespace App\Http\Middleware;

use App\Support\BotGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Quietly turns away form posts that trip a bot trap (see App\Support\BotGuard).
 * Nothing is saved and no email is sent; the visitor just sees the form again
 * with a neutral "please try again" (so a real person who was too quick can
 * simply submit again).
 */
class GuardAgainstBots
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') || ! ($reason = BotGuard::reason($request))) {
            return $next($request);
        }

        Log::notice("Bot trap: {$reason}", ['path' => $request->path(), 'ip' => $request->ip()]);

        $message = 'Please check your details and try again.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()
            ->withInput($request->except([BotGuard::TRAP_FIELD, BotGuard::TIME_FIELD, 'otp', 'password', 'cf-turnstile-response']))
            ->with('error', $message);
    }
}
