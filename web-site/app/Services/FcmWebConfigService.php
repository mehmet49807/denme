<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FcmWebConfigService
{
    private const CONFIG_PATH = 'app/firebase/web-config.json';

    private const DEFAULT_VAPID = 'BABfPUFug84XcERSekZDFUko8lGgOUyYrPXNdV9wTyzEeZ9wmm52bT0oTFyt1BiNY0dT44EkAdrGR1Ma-gPlfXE';

    /**
     * @return array{
     *   enabled: bool,
     *   configured: bool,
     *   vapid_key: string,
     *   apiKey?: string,
     *   authDomain?: string,
     *   projectId?: string,
     *   storageBucket?: string,
     *   messagingSenderId?: string,
     *   appId?: string,
     *   measurementId?: string
     * }
     */
    public function publicConfig(): array
    {
        $stored = $this->loadStored();
        $projectId = (string) ($stored['projectId'] ?? config('firebase.project_id', 'gonulkoprusu-325eb'));
        $vapid = (string) (
            $stored['vapidKey']
            ?? config('firebase.web.vapid_key')
            ?? env('FIREBASE_VAPID_KEY')
            ?? self::DEFAULT_VAPID
        );

        $apiKey = (string) ($stored['apiKey'] ?? config('firebase.web.api_key', env('FIREBASE_WEB_API_KEY', '')));
        $appId = (string) ($stored['appId'] ?? config('firebase.web.app_id', env('FIREBASE_WEB_APP_ID', '')));
        $senderId = (string) ($stored['messagingSenderId'] ?? config('firebase.web.messaging_sender_id', env('FIREBASE_MESSAGING_SENDER_ID', '')));

        $configured = $apiKey !== '' && $appId !== '' && $senderId !== '' && $vapid !== '';

        return array_filter([
            'enabled' => (bool) config('firebase.web.enabled', true),
            'configured' => $configured,
            'apiKey' => $apiKey,
            'authDomain' => (string) ($stored['authDomain'] ?? "{$projectId}.firebaseapp.com"),
            'projectId' => $projectId,
            'storageBucket' => (string) ($stored['storageBucket'] ?? "{$projectId}.appspot.com"),
            'messagingSenderId' => $senderId,
            'appId' => $appId,
            'measurementId' => (string) ($stored['measurementId'] ?? ''),
            'vapidKey' => $vapid,
        ], fn ($v) => $v !== '' && $v !== null);
    }

    public function isReady(): bool
    {
        $cfg = $this->publicConfig();

        return ! empty($cfg['configured']) && ! empty($cfg['enabled']);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{ok: bool, error?: string, config?: array<string, mixed>}
     */
    public function saveManual(array $input): array
    {
        $apiKey = trim((string) ($input['apiKey'] ?? $input['api_key'] ?? ''));
        $appId = trim((string) ($input['appId'] ?? $input['app_id'] ?? ''));
        $senderId = trim((string) ($input['messagingSenderId'] ?? $input['messaging_sender_id'] ?? ''));
        $projectId = trim((string) ($input['projectId'] ?? $input['project_id'] ?? config('firebase.project_id', 'gonulkoprusu-325eb')));
        $vapid = trim((string) ($input['vapidKey'] ?? $input['vapid_key'] ?? self::DEFAULT_VAPID));

        if ($apiKey === '' || $appId === '' || $senderId === '') {
            return [
                'ok' => false,
                'error' => 'apiKey, appId ve messagingSenderId gerekli (Firebase Console → Project settings → Web app → Config).',
            ];
        }

        $payload = [
            'apiKey' => $apiKey,
            'authDomain' => trim((string) ($input['authDomain'] ?? "{$projectId}.firebaseapp.com")),
            'projectId' => $projectId,
            'storageBucket' => trim((string) ($input['storageBucket'] ?? "{$projectId}.appspot.com")),
            'messagingSenderId' => $senderId,
            'appId' => $appId,
            'measurementId' => trim((string) ($input['measurementId'] ?? '')),
            'vapidKey' => $vapid !== '' ? $vapid : self::DEFAULT_VAPID,
            'synced_at' => now()->toIso8601String(),
            'source' => 'manual',
        ];

        return $this->writeStored($payload);
    }

    /**
     * Service account ile Firebase Management API'den web app config çek / oluştur.
     *
     * @return array{ok: bool, error?: string, created?: bool, config?: array<string, mixed>}
     */
    public function syncFromFirebaseApi(): array
    {
        $accessToken = $this->googleAccessToken([
            'https://www.googleapis.com/auth/firebase',
            'https://www.googleapis.com/auth/cloud-platform',
        ]);

        if ($accessToken === null) {
            return ['ok' => false, 'error' => 'Service account erişim jetonu alınamadı — FCM JSON yüklü mü?'];
        }

        $projectId = (string) config('firebase.project_id', 'gonulkoprusu-325eb');
        $base = "https://firebase.googleapis.com/v1beta1/projects/{$projectId}";

        try {
            $list = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(20)
                ->get($base.'/webApps');

            if (! $list->successful()) {
                return [
                    'ok' => false,
                    'error' => 'Web app listesi alınamadı: '.($list->json('error.message') ?? $list->status()),
                ];
            }

            $apps = $list->json('apps') ?? [];
            $created = false;
            $appId = null;

            if (! is_array($apps) || $apps === []) {
                $create = Http::withToken($accessToken)
                    ->acceptJson()
                    ->timeout(20)
                    ->post($base.'/webApps', [
                        'displayName' => 'Gonul Koprusu Web',
                    ]);

                if (! $create->successful()) {
                    return [
                        'ok' => false,
                        'error' => 'Web app oluşturulamadı: '.($create->json('error.message') ?? $create->status()),
                    ];
                }

                $appId = (string) ($create->json('appId') ?? '');
                $created = true;

                // Yeni web app config hemen hazır olmayabilir.
                usleep(800000);
            } else {
                $appId = (string) ($apps[0]['appId'] ?? '');
            }

            if ($appId === '') {
                return ['ok' => false, 'error' => 'Web appId boş.', 'apps_count' => is_array($apps) ? count($apps) : 0];
            }

            // appId içinde ":" var — path segment encode şart.
            $configUrl = $base.'/webApps/'.rawurlencode($appId).'/config';
            $configResp = null;
            for ($attempt = 0; $attempt < 4; $attempt++) {
                $configResp = Http::withToken($accessToken)
                    ->acceptJson()
                    ->timeout(20)
                    ->get($configUrl);
                if ($configResp->successful()) {
                    break;
                }
                usleep(700000);
            }

            if (! $configResp || ! $configResp->successful()) {
                return [
                    'ok' => false,
                    'error' => 'Web config alınamadı: '.($configResp?->json('error.message') ?? $configResp?->status() ?? 'no response'),
                    'appId' => $appId,
                ];
            }

            $cfg = $configResp->json();
            if (! is_array($cfg)) {
                return ['ok' => false, 'error' => 'Web config JSON değil.'];
            }

            $payload = [
                'apiKey' => (string) ($cfg['apiKey'] ?? ''),
                'authDomain' => (string) ($cfg['authDomain'] ?? "{$projectId}.firebaseapp.com"),
                'projectId' => (string) ($cfg['projectId'] ?? $projectId),
                'storageBucket' => (string) ($cfg['storageBucket'] ?? "{$projectId}.appspot.com"),
                'messagingSenderId' => (string) ($cfg['messagingSenderId'] ?? ''),
                'appId' => (string) ($cfg['appId'] ?? ''),
                'measurementId' => (string) ($cfg['measurementId'] ?? ''),
                'vapidKey' => (string) (config('firebase.web.vapid_key') ?: self::DEFAULT_VAPID),
                'synced_at' => now()->toIso8601String(),
                'source' => 'firebase_api',
                'web_app_id' => $appId,
            ];

            if ($payload['apiKey'] === '' || $payload['appId'] === '' || $payload['messagingSenderId'] === '') {
                return ['ok' => false, 'error' => 'API config eksik alan döndürdü.', 'config' => $payload];
            }

            $write = $this->writeStored($payload);
            if (! ($write['ok'] ?? false)) {
                return $write;
            }

            return [
                'ok' => true,
                'created' => $created,
                'config' => $this->publicConfig(),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loadStored(): array
    {
        $path = storage_path(self::CONFIG_PATH);
        if (! is_readable($path)) {
            // env fallbacks only
            return array_filter([
                'apiKey' => env('FIREBASE_WEB_API_KEY'),
                'appId' => env('FIREBASE_WEB_APP_ID'),
                'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID'),
                'projectId' => env('FIREBASE_PROJECT_ID'),
                'vapidKey' => env('FIREBASE_VAPID_KEY', self::DEFAULT_VAPID),
            ]);
        }

        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? $json : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, error?: string, config?: array<string, mixed>}
     */
    private function writeStored(array $payload): array
    {
        $path = storage_path(self::CONFIG_PATH);
        $dir = dirname($path);
        if (! is_dir($dir) && ! @mkdir($dir, 0750, true) && ! is_dir($dir)) {
            return ['ok' => false, 'error' => 'firebase dizini oluşturulamadı.'];
        }

        $pretty = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (! is_string($pretty) || @file_put_contents($path, $pretty."\n") === false) {
            return ['ok' => false, 'error' => 'web-config.json yazılamadı.'];
        }
        @chmod($path, 0640);

        return ['ok' => true, 'config' => $this->publicConfig()];
    }

    /**
     * @param  list<string>  $scopes
     */
    private function googleAccessToken(array $scopes): ?string
    {
        try {
            $fcm = app(FcmPushService::class);
            $status = $fcm->status();
            if (! ($status['configured'] ?? false)) {
                return null;
            }

            $path = $status['credentials_path'] ?? null;
            $creds = null;
            if (is_string($path) && is_readable($path)) {
                $creds = json_decode((string) file_get_contents($path), true);
            }
            if (! is_array($creds)) {
                $inline = env('FIREBASE_CREDENTIALS_JSON');
                $creds = is_string($inline) ? json_decode($inline, true) : null;
            }
            if (! is_array($creds) || empty($creds['private_key']) || empty($creds['client_email'])) {
                return null;
            }

            $now = time();
            $header = $this->b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = $this->b64url(json_encode([
                'iss' => $creds['client_email'],
                'scope' => implode(' ', $scopes),
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));
            $input = $header.'.'.$claims;
            $signature = '';
            if (! openssl_sign($input, $signature, $creds['private_key'], OPENSSL_ALGO_SHA256)) {
                return null;
            }
            $jwt = $input.'.'.$this->b64url($signature);

            $response = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            $token = $response->json('access_token');

            return is_string($token) && $token !== '' ? $token : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
