<?php

return [
    'enable'  => true,
    // 默认缓存驱动
    'default' => env('CACHE_TYPE', 'file'),
    // 缓存连接方式配置
    'stores'  => [
        // 文件缓存
        'file'  => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => env('CACHE_EXPIRE', 0),
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // redis缓存
        'redis' => [
            // 驱动方式
            'type'       => 'redis',
            // 服务器地址
            'host'       => env('CACHE_REDIS_HOST', env('REDIS_HOST', '127.0.0.1')),
            // 服务器端口
            'port'       => env('CACHE_REDIS_PORT', env('REDIS_PORT', 6379)),
            // 连接密码
            'password'   => env('CACHE_REDIS_PASSWORD', env('REDIS_PASSWORD', '')),
            'select'     => env('CACHE_REDIS_DB', env('REDIS_DB', 0)),
            'timeout'    => 0,
            // 缓存有效期 0表示永久缓存
            'expire'     => env('CACHE_EXPIRE', 0),
            'persistent' => false,
            // 缓存前缀
            'prefix'     => env('CACHE_PREFIX', ''),
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
        ],
        // 更多的缓存连接
    ],
];