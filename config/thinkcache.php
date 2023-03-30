<?php
return [
    'default' => env('CACHE_TYPE', 'file'),
    'stores' => [
        'file' => [
            'type' => 'File',
            // 缓存保存目录
            'path' => runtime_path() . '/cache/',
            // 缓存前缀
            'prefix' => env('CACHE_PREFIX', ''),
            // 缓存有效期 0表示永久缓存
            'expire' => env('CACHE_EXPIRE', 0)
        ],
        'redis' => [
            'type' => 'redis',
            'host' => env('CACHE_REDIS_HOST', env('REDIS_HOST', '127.0.0.1')),
            'port' => env('CACHE_REDIS_PORT', env('REDIS_PORT', 6379)),
            'password' => env('CACHE_REDIS_PASSWORD', env('REDIS_PASSWORD', '')),
            'select' => env('CACHE_REDIS_DB', env('REDIS_DB', '')),
            'prefix' => env('CACHE_PREFIX', ''),
            'expire' => env('CACHE_EXPIRE', 0),
            //指定序列化方式 -兼容php8.1报错
            'serialize' => ['serialize', 'unserialize']
        ],
    ],
];