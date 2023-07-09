<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD'),
        'database' => env('REDIS_DB', 0),
    ],
    'cache' => [
        'host' => env('CACHE_REDIS_HOST', env('REDIS_HOST', '127.0.0.1')),
        'port' => env('CACHE_REDIS_PORT', env('REDIS_PORT', 6379)),
        'password' => env('CACHE_REDIS_PASSWORD', env('REDIS_PASSWORD')),
        'database' => env('CACHE_REDIS_DB', env('REDIS_DB', 0)),
    ]
];
