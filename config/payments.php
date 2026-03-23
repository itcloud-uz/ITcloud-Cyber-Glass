<?php

return [
    'payme' => [
        'ips' => [
            '195.158.31.134', // Real Payme IP range example
            '195.158.31.135',
            '127.0.0.1',      // For local testing
        ],
        'token' => env('PAYME_SECRET_KEY', 'default_secret')
    ],
    'click' => [
        'ips' => [
            '185.178.209.0/24', // Real Click IP range example
            '127.0.0.1'
        ],
        'secret' => env('CLICK_SECRET_KEY', 'default_secret')
    ]
];
