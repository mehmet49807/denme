<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase / FCM HTTP v1
    |--------------------------------------------------------------------------
    |
    | Service account JSON: Firebase Console → Project settings → Service accounts
    | → Generate new private key. Sunucuya yükle:
    | storage/app/firebase/gonulkoprusu-325eb.json
    |
    | Alternatif: FIREBASE_CREDENTIALS_JSON env (ham JSON string).
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID', 'gonulkoprusu-325eb'),

    'credentials' => env(
        'FIREBASE_CREDENTIALS',
        storage_path('app/firebase/gonulkoprusu-325eb.json')
    ),

    'credential_fallbacks' => array_values(array_filter([
        env('FIREBASE_CREDENTIALS_FALLBACK'),
        '/home/gonulkop/public_html/storage/app/firebase/gonulkoprusu-325eb.json',
        '/home/gonulkop/admin.gonulkoprusu.com/storage/app/firebase/gonulkoprusu-325eb.json',
        storage_path('app/firebase/service-account.json'),
        storage_path('app/firebase/firebase-adminsdk.json'),
    ])),

    /*
    |--------------------------------------------------------------------------
    | FCM Web Push (tarayıcı)
    |--------------------------------------------------------------------------
    |
    | VAPID / Web Push certificates key (Firebase Console → Cloud Messaging).
    | apiKey / appId / messagingSenderId: setup/fcm-web ile API’den çekilir
    | veya FIREBASE_WEB_* env ile verilir.
    |
    */
    'web' => [
        'enabled' => env('FIREBASE_WEB_PUSH_ENABLED', true),
        'vapid_key' => env(
            'FIREBASE_VAPID_KEY',
            'BABfPUFug84XcERSekZDFUko8lGgOUyYrPXNdV9wTyzEeZ9wmm52bT0oTFyt1BiNY0dT44EkAdrGR1Ma-gPlfXE'
        ),
        'api_key' => env('FIREBASE_WEB_API_KEY'),
        'app_id' => env('FIREBASE_WEB_APP_ID'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    ],

];
