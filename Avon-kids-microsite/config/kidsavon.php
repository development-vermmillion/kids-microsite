<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo rider
    |--------------------------------------------------------------------------
    |
    | Until mobile + OTP login is built, pages are shown for this rider.
    | It is created by the KidsAvonDemoSeeder ("Alex Rider").
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
