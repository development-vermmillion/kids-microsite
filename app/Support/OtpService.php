<?php

namespace App\Support;

use App\Mail\OtpCodeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails one-time codes for registering ("register") and logging in ("login"),
 * and checks them.
 *
 * Codes are 6 digits, valid for 10 minutes, allow 5 tries, and a new code can be
 * requested once a minute. Only a hash of the code is stored.
 *
 * Local testing: while OTP_TEST_CODE is set, every code is that value and no
 * email is sent.
 */
class OtpService
{
    public const PURPOSES = ['register', 'login'];

    public static function normaliseEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    public static function testCode(): ?string
    {
        $code = config('kidsavon.otp.test_code');

        return $code !== null && $code !== '' ? (string) $code : null;
    }

    public static function expiresMinutes(): int
    {
        return (int) config('kidsavon.otp.expires_minutes', 10);
    }

    /**
     * Creates a fresh code (replacing any earlier one) and emails it.
     * Returns null when sent, otherwise a message to show the rider.
     */
    public function send(string $email, string $purpose, ?string $name = null): ?string
    {
        $email = self::normaliseEmail($email);
        $existing = DB::table('otp_codes')->where(compact('email', 'purpose'))->first();

        $wait = (int) config('kidsavon.otp.resend_seconds', 60);
        if ($existing && now()->diffInSeconds($existing->created_at, true) < $wait) {
            $left = $wait - (int) now()->diffInSeconds($existing->created_at, true);

            return "We just sent a code. You can ask for a new one in {$left} seconds.";
        }

        $length = (int) config('kidsavon.otp.length', 6);
        $code = self::testCode() ?? str_pad((string) random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);

        DB::table('otp_codes')->updateOrInsert(
            compact('email', 'purpose'),
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::expiresMinutes()),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        if (self::testCode()) {
            return null;
        }

        try {
            Mail::to($email)->send(new OtpCodeMail($code, $purpose, $name, self::expiresMinutes()));
        } catch (Throwable $e) {
            report($e);
            Log::error("OTP email to {$email} could not be sent: ".$e->getMessage());
            DB::table('otp_codes')->where(compact('email', 'purpose'))->delete();

            return 'We could not send the email right now. Please try again in a minute.';
        }

        return null;
    }

    /**
     * Checks a code. Returns null when it is correct (and uses it up),
     * otherwise a message to show the rider.
     */
    public function check(string $email, string $purpose, ?string $code): ?string
    {
        $email = self::normaliseEmail($email);
        $row = DB::table('otp_codes')->where(compact('email', 'purpose'))->first();

        if (! $row) {
            return 'Please tap “Send OTP” first and check your email.';
        }

        if (now()->greaterThan($row->expires_at)) {
            return 'This code has expired. Please send a new one.';
        }

        if ($row->attempts >= (int) config('kidsavon.otp.max_attempts', 5)) {
            return 'Too many wrong tries. Please send a new code.';
        }

        if (! Hash::check(preg_replace('/\s+/', '', (string) $code), $row->code_hash)) {
            DB::table('otp_codes')->where('id', $row->id)->increment('attempts');

            return 'That code is not right. Please check your email and try again.';
        }

        DB::table('otp_codes')->where('id', $row->id)->delete();

        return null;
    }
}
