<?php

declare(strict_types=1);

$defaults = [
    'app_name' => 'Chicken',
    'app_url' => 'https://gonulkoprusu.com/chicken',
    'timezone' => 'Europe/Istanbul',
    'db' => [
        'host' => getenv('CHICKEN_DB_HOST') ?: 'localhost',
        'port' => (int) (getenv('CHICKEN_DB_PORT') ?: 3306),
        'name' => getenv('CHICKEN_DB_NAME') ?: 'gonulkop_chicken',
        'user' => getenv('CHICKEN_DB_USER') ?: 'gonulkop_admin',
        'pass' => getenv('CHICKEN_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'session_name' => 'chicken_session',
];

$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (is_array($local)) {
        $defaults = array_replace_recursive($defaults, $local);
    }
}

return $defaults;
