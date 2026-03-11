<?php

return [

    'paths' => ['api/*', 'oauth/token', 'oauth/authorize'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Add each client app domain here, e.g.:
        // 'https://app1.yourdomain.com',
        // 'https://app2.yourdomain.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
