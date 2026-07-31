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
        'public_url' => env('ODYSSEUS_PUBLIC_URL', 'https://odysseus.gonulkoprusu.com'),
        'user' => env('ODYSSEUS_USER', 'admin'),
        'password' => env('ODYSSEUS_PASSWORD'),
        'workspace' => env('ODYSSEUS_WORKSPACE'),
        // Model API key’leri admin .env’de tutulmaz; Odysseus Settings’ten okunur.
        'endpoint_id' => env('ODYSSEUS_ENDPOINT_ID'),
        'model' => env('ODYSSEUS_MODEL'),
        'timeout' => env('ODYSSEUS_TIMEOUT', 300),
    ],

    'seo' => [
        'sync_key' => env('SEO_SYNC_KEY'),
        'frontend_storage_path' => env('SEO_FRONTEND_STORAGE_PATH'),
    ],

];
