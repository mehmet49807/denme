<?php

declare(strict_types=1);

/**
 * Copy to config.local.php on the server and fill credentials.
 * config.local.php is gitignored.
 */
return [
    'app_name' => 'Crisp & Co.',
    'ops_secret' => 'change-me-ops-secret',
    'app_url' => 'https://gonulkoprusu.com/chicken',
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
