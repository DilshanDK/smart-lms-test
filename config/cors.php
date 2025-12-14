<?php

return [
    'paths' => ['api/*', 'auth/*', 'login', 'logout', 'oauth/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://smart-lms-test.onrender.com',
        'https://accounts.google.com', // Add Google's domain
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
