<?php

declare(strict_types=1);

/**
 * Copy to config.local.php on the server and fill credentials.
 * config.local.php is gitignored.
 */
return [
    'app_name' => 'Chicken',
    'app_url' => 'https://chicken.gonulkoprusu.com',
    'timezone' => 'Europe/Istanbul',
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'gonulkop_chicken',
        'user' => 'gonulkop_admin',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'session_name' => 'chicken_session',
];
