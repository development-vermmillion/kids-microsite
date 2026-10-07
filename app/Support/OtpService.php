<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Sends and checks one-time passwords for a mobile number.
 *
 * Test mode: while OTP_TEST_CODE is set (default 1234) every OTP is that code
 * and no SMS is sent. To go live, set OTP_TEST_CODE= (empty) in .env and fill
 * in sendSms() with your SMS provider (MSG91, Twilio, etc.).
 */
class OtpService
{
    public const EXPIRES_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public static function normalise(?string $mobile): string
    {
        return preg_replace('/[^0-9+]/', '', (string) $mobile);
    }

    public static function testCode(): ?string
    {
        $code = config('kidsavon.otp.test_code');

        return $code !== null && $code !== '' ? (string) $code : null;
    }

    /** Creates a fresh code for the number (replacing any earlier one) and sends it. */
    public function send(string $mobile): void
    {
        $mobile = self::normalise($mobile);
        $code = self::testCode() ?? (string) random_int(1000, 9999);

        DB::table('otp_codes')->updateOrInsert(
            ['mobile' => $mobile],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        if (! self::testCode()) {
            $this->sendSms($mobile, "Your Kids Avon OTP is {$code}. It is valid for ".self::EXPIRES_MINUTES.' minutes.');
        }
    }

    /**
     * Checks a code. Returns null when it is correct (and uses it up),
     * otherwise a message to show the rider.
     */
    public function check(string $mobile, ?string $code): ?string
    {
        $mobile = self::normalise($mobile);
        $row = DB::table('otp_codes')->where('mobile', $mobile)->first();

        if (! $row) {
            return 'Please tap “Send OTP” first.';
        }

        if (now()->greaterThan($row->expires_at)) {
            return 'This OTP has expired. Please send a new one.';
        }

        if ($row->attempts >= self::MAX_ATTEMPTS) {
            return 'Too many wrong tries. Please send a new OTP.';
        }

        if (! Hash::check(trim((string) $code), $row->code_hash)) {
            DB::table('otp_codes')->where('id', $row->id)->increment('attempts');

            return 'That OTP is not right. Please check and try again.';
        }

        DB::table('otp_codes')->where('id', $row->id)->delete();

        return null;
    }

    /** Hook for a real SMS provider. Until one is connected the message is only logged. */
    protected function sendSms(string $mobile, string $message): void
    {
        Log::info("OTP SMS to {$mobile}: {$message}");
    }
}
