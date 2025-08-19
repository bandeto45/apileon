<?php

return [
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'key' => $_ENV['APP_KEY'] ?? '',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => 'UTC',
    
    'middleware' => [
        'global' => [
            // Global middleware applied to all routes
        ],
        
        'groups' => [
            'api' => [
                'cors',
                'throttle'
            ]
        ]
    ]
];
