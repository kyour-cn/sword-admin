<?php

use Dotenv\Dotenv;

if (is_file(__DIR__ . '/.env') && class_exists(Dotenv::class)) {
    Dotenv::createUnsafeImmutable(__DIR__)->safeLoad();
}

$database = require __DIR__ . '/config/database.php';
$default = $database['default'] ?? 'mysql';
$connection = $database['connections'][$default] ?? [];

return [
    'paths' => [
        'migrations' => 'database/migrations',
        'seeds' => 'database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migration_log',
        'default_environment' => $default,
        $default => [
            'adapter' => $connection['driver'] ?? 'mysql',
            'host' => $connection['host'] ?? '127.0.0.1',
            'name' => $connection['database'] ?? '',
            'user' => $connection['username'] ?? '',
            'pass' => $connection['password'] ?? '',
            'port' => $connection['port'] ?? 3306,
            'charset' => $connection['charset'] ?? 'utf8mb4',
            'collation' => $connection['collation'] ?? 'utf8mb4_general_ci',
        ],
    ],
    'version_order' => 'creation',
];
