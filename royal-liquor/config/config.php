<?php

declare(strict_types=1);

/**
 * Application Configuration
 * 
 * Central configuration file for the application
 */

return [
    'app' => [
        'name' => 'Royal Liquor API',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'timezone' => 'Asia/Colombo',
    ],

    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['DB_PORT'] ?? 5432),
        'name' => $_ENV['DB_NAME'] ?? 'royal-liquor',
        'user' => $_ENV['DB_USER'] ?? 'postgres',
        'pass' => $_ENV['DB_PASS'] ?? '18499',
        'charset' => 'utf8',
    ],

    'cache' => [
        'enabled' => filter_var($_ENV['CACHE_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'path' => __DIR__ . '/../storage/cache',
        'default_ttl' => 3600, // 1 hour
    ],

    'logging' => [
        'enabled' => true,
        'path' => __DIR__ . '/../logs',
        'level' => $_ENV['LOG_LEVEL'] ?? 'debug', // debug, info, warning, error, critical
        'max_files' => 30, // Keep logs for 30 days
    ],

    'security' => [
        'csrf_enabled' => true,
        'csrf_secret' => $_ENV['CSRF_SECRET'] ?? 'change-this-in-production',
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'change-this-in-production',
        'jwt_expiry' => 7200, // 2 hours
        'password_algo' => PASSWORD_BCRYPT,
        'password_cost' => 12,
    ],

    'rate_limit' => [
        'enabled' => filter_var($_ENV['RATE_LIMIT_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'max_requests' => 100,
        'window_seconds' => 3600, // 1 hour
        'storage_path' => __DIR__ . '/../storage/rate_limits',
    ],

    'session' => [
        'lifetime' => 7200, // 2 hours
        'name' => 'ROYAL_SESSION',
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['http://localhost:3000', 'http://localhost:8000'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token'],
        'allow_credentials' => true,
        'max_age' => 3600,
    ],

    'pagination' => [
        'default_limit' => 50,
        'max_limit' => 100,
    ],
];
