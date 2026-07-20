<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel güncelleme hedefleri
    |--------------------------------------------------------------------------
    |
    | Laravel 13 için PHP 8.3+ gerekir.
    |
    */

    'target_constraint' => env('LARAVEL_TARGET_CONSTRAINT', '^13.0'),
    'target_major' => (int) env('LARAVEL_TARGET_MAJOR', 13),

    'packagist_url' => 'https://packagist.org/packages/laravel/framework.json',

    'setup_key' => env('LARAVEL_UPDATE_KEY', 'gk-laravel-update-2026'),

    'web_url' => env('DEPLOY_WEB_URL', 'https://gonulkoprusu.com'),
    'admin_url' => env('DEPLOY_ADMIN_URL', 'https://admin.gonulkoprusu.com'),

    'history_limit' => 20,

];
