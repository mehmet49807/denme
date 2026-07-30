<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'odysseus' => [
        'url' => env('ODYSSEUS_URL', 'http://127.0.0.1:7000'),
        'user' => env('ODYSSEUS_USER', 'admin'),
        'password' => env('ODYSSEUS_PASSWORD'),
        'workspace' => env('ODYSSEUS_WORKSPACE'),
        'model' => env('ODYSSEUS_MODEL'),
        'endpoint_url' => env('ODYSSEUS_ENDPOINT_URL'),
        'api_key' => env('ODYSSEUS_API_KEY'),
        'timeout' => env('ODYSSEUS_TIMEOUT', 300),
    ],

    'seo' => [
        'sync_key' => env('SEO_SYNC_KEY'),
        'frontend_storage_path' => env('SEO_FRONTEND_STORAGE_PATH'),
    ],

];
