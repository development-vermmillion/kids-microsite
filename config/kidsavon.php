<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email OTP (registration and login)
    |--------------------------------------------------------------------------
    |
    | Codes are emailed through the SMTP server set in the MAIL_* settings.
    | test_code is for local testing only: while it is set, every OTP is that
    | code and no email is sent. Leave OTP_TEST_CODE empty on the live site.
    |
    */

    'otp' => [
        'test_code' => env('OTP_TEST_CODE'),
        'length' => 6,
        'expires_minutes' => 10,
        'max_attempts' => 5,
        'resend_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile (robot check)
    |--------------------------------------------------------------------------
    |
    | Create a widget at dash.cloudflare.com > Turnstile and paste its two keys
    | into .env. The 1x0000… keys in .env.example are Cloudflare's official
    | testing keys: they always pass, so replace them before going live.
    | With no site key at all, the robot check is switched off.
    |
    */

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bot trap
    |--------------------------------------------------------------------------
    |
    | Forms carry an invisible "trap" field that only bots fill in, and a
    | signed timestamp: anything sent back faster than this is a bot.
    |
    */

    'bot_guard' => [
        'min_seconds' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo rider
    |--------------------------------------------------------------------------
    |
    | Email of the demo rider ("Alex Rider") created by KidsAvonDemoSeeder.
    |
    */

    'demo_rider_email' => env('DEMO_RIDER_EMAIL', 'alex@kidsavon.test'),
    'demo_rider_mobile' => env('DEMO_RIDER_MOBILE', '9999999999'),

    /*
    |--------------------------------------------------------------------------
    | Footer quick links
    |--------------------------------------------------------------------------
    */

    'footer_links' => [
        'home' => [
            ['label' => 'Avon Cycles', 'url' => '#'],
            ['label' => 'Avon Fitness', 'url' => '#'],
            ['label' => 'Avon E-bike', 'url' => '#'],
        ],
        'default' => [
            ['label' => 'How do I participate?', 'url' => '#'],
            ['label' => 'Do I need Strava?', 'url' => '#'],
            ['label' => 'Eligible Activities', 'url' => '#'],
            ['label' => 'Privacy & Data', 'url' => '#'],
        ],
    ],

];
