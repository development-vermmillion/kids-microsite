<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cloudflare Turnstile robot check.
 *
 * The page shows Cloudflare's widget, which adds a token to the form; the
 * server asks Cloudflare whether that token is genuine (each token works once,
 * for 5 minutes). With no site key configured the check is switched off.
 */
class Turnstile
{
    public const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Cloudflare's documented testing secret keys and their fixed answers. They
     * never reach a real visitor, so they are answered here without a network call.
     */
    private const TEST_SECRETS = [
        '1x0000000000000000000000000000000AA' => true,  // always passes
        '2x0000000000000000000000000000000AA' => false, // always fails
        '3x0000000000000000000000000000000AA' => false, // "token already spent"
    ];

    public static function enabled(): bool
    {
        return filled(config('kidsavon.turnstile.site_key'));
    }

    public static function siteKey(): ?string
    {
        return config('kidsavon.turnstile.site_key');
    }

    public static function usingTestKeys(): bool
    {
        return self::enabled() && array_key_exists((string) config('kidsavon.turnstile.secret_key'), self::TEST_SECRETS);
    }

    /**
     * Returns null when the visitor passed, otherwise a message to show.
     *
     * As Cloudflare recommends, a genuine token must also come from our own
     * website (hostname) and from the right form (action), so a token solved
     * on another site or another form can't be reused here.
     *
     * @param  string[]  $actions  form names allowed here, e.g. ['login']
     */
    public static function check(?string $token, ?string $ip = null, array $actions = []): ?string
    {
        if (! self::enabled()) {
            return null;
        }

        $message = 'Please complete the robot check (tick the box above the button) and try again.';

        if (blank($token)) {
            return $message;
        }

        $secret = (string) config('kidsavon.turnstile.secret_key');

        if (array_key_exists($secret, self::TEST_SECRETS)) {
            return self::TEST_SECRETS[$secret] ? null : $message;
        }

        try {
            $result = Http::asForm()->timeout(8)->retry(1, 300, throw: false)->post(self::VERIFY_URL, [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ])->json();
        } catch (Throwable $e) {
            Log::warning('Turnstile check could not reach Cloudflare: '.$e->getMessage());

            return 'The robot check could not be completed. Please try again.';
        }

        if (! ($result['success'] ?? false)) {
            Log::info('Turnstile check failed', ['errors' => $result['error-codes'] ?? null, 'ip' => $ip]);

            return $message;
        }

        $hostnames = (array) config('kidsavon.turnstile.hostnames', []);
        if ($hostnames && ! in_array($result['hostname'] ?? null, $hostnames, true)) {
            Log::warning('Turnstile token from another website', ['hostname' => $result['hostname'] ?? null, 'ip' => $ip]);

            return $message;
        }

        if ($actions && ! in_array($result['action'] ?? null, $actions, true)) {
            Log::warning('Turnstile token from another form', ['action' => $result['action'] ?? null, 'expected' => $actions, 'ip' => $ip]);

            return $message;
        }

        return null;
    }
}
