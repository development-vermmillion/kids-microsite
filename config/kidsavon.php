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
        // Website addresses a token may come from (comma separated). Empty = the
        // APP_URL domain with and without "www.". Tokens from anywhere else are refused.
        'hostnames' => array_values(array_filter(array_map('trim', explode(',', (string) env('TURNSTILE_HOSTNAMES', '')))))
            ?: (($host = parse_url((string) env('APP_URL'), PHP_URL_HOST)) ? array_values(array_unique([$host, preg_replace('/^www\./', '', $host), 'www.'.preg_replace('/^www\./', '', $host)])) : []),
    ],

    /*
    |--------------------------------------------------------------------------
    | Visitors' real IP address behind Cloudflare
    |--------------------------------------------------------------------------
    |
    | When the domain's DNS is proxied by Cloudflare (orange cloud), every
    | request arrives from a Cloudflare server. Trusting Cloudflare's published
    | address ranges (https://www.cloudflare.com/ips/) lets the site see each
    | visitor's real IP, so rate limits work per visitor. Add any extra proxy
    | of your host in TRUSTED_PROXIES (comma separated).
    |
    */

    'trusted_proxies' => array_merge([
        // IPv4
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        // IPv6
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
        '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ], array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '')))))),

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

    /*
    |--------------------------------------------------------------------------
    | First admin account
    |--------------------------------------------------------------------------
    |
    | Created by `php artisan kidsavon:setup` (and the seeder) when there is no
    | admin yet. If no password is set, a random one is made and printed once.
    |
    */

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@kidsavon.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],

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
