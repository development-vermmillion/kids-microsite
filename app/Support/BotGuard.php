<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Two invisible bot traps added to forms by <x-bot-guard />:
 *  - a "trap" field hidden from people; bots that fill in every box fill it in too
 *  - a signed time stamp of when the form was shown; forms sent back in under
 *    2 seconds were filled in by a program, not a person
 */
class BotGuard
{
    public const TRAP_FIELD = 'kv_website';

    public const TIME_FIELD = 'kv_ts';

    public static function stamp(): string
    {
        return Crypt::encryptString((string) microtime(true));
    }

    /** Returns null for a person, otherwise why the request looks like a bot. */
    public static function reason(Request $request): ?string
    {
        if (filled($request->input(self::TRAP_FIELD))) {
            return 'trap field filled';
        }

        try {
            $shownAt = (float) Crypt::decryptString((string) $request->input(self::TIME_FIELD));
        } catch (DecryptException) {
            return 'missing or altered time stamp';
        }

        if (microtime(true) - $shownAt < (float) config('kidsavon.bot_guard.min_seconds', 2)) {
            return 'sent too fast';
        }

        return null;
    }
}
