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

    /*
    | Kardeş uygulama yolları (aynı cPanel hesabı). Admin yapılandırması
    | web credentials dosyasını da deneyebilir.
    */
    'credential_fallbacks' => array_values(array_filter([
        env('FIREBASE_CREDENTIALS_FALLBACK'),
        '/home/gonulkop/public_html/storage/app/firebase/gonulkoprusu-325eb.json',
        '/home/gonulkop/admin.gonulkoprusu.com/storage/app/firebase/gonulkoprusu-325eb.json',
        storage_path('app/firebase/service-account.json'),
        storage_path('app/firebase/firebase-adminsdk.json'),
    ])),

];
