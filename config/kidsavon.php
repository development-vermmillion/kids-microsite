<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP login
    |--------------------------------------------------------------------------
    |
    | While test_code is set, every OTP is this code and no SMS is sent.
    | Before launch, set OTP_TEST_CODE= (empty) in .env and connect an SMS
    | provider in app/Support/OtpService.php.
    |
    */

    'otp' => [
        'test_code' => env('OTP_TEST_CODE', '1234'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo rider
    |--------------------------------------------------------------------------
    |
    | Mobile number of the demo rider ("Alex Rider") created by the
    | KidsAvonDemoSeeder. Log in with it and the test OTP to try the site.
    |
    */

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
