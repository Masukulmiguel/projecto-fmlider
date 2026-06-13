<?php

function env($key, $default = '')
{
    $v = getenv($key);
    if ($v !== false) return $v;
    if (isset($_ENV[$key])) return $_ENV[$key];
    $envFile = dirname(__DIR__) . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, $key . '=') === 0) {
                return trim(substr($line, strlen($key) + 1));
            }
        }
    }
    return $default;
}

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => (int) env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'fmlider'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
    ],
];
