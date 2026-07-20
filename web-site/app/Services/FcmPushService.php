<?php

namespace App\Services;

use App\Models\AdminBroadcast;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class FcmPushService
{
    private ?array $credentials = null;

    private ?string $credentialsSource = null;

    public function isConfigured(): bool
    {
        return $this->loadCredentials() !== null;
    }

    /**
     * @return array{configured: bool, project_id: string, credentials_path: ?string, credentials_source: string, device_tokens_table: bool, device_count: int, openssl: bool, hints: list<string>}
     */
    public function status(): array
    {
        $creds = $this->loadCredentials();
        $path = $this->resolveCredentialsPath();
        $hints = [];

        if ($creds === null) {
            $hints[] = 'Firebase service account JSON eksik.';
            $hints[] = 'Yükle: storage/app/firebase/gonulkoprusu-325eb.json';
            $hints[] = 'veya Sistem Sağlığı sayfasından JSON yükleyin.';
        }

        return [
            'configured' => $creds !== null,
            'project_id' => (string) (
                $creds['project_id']
                ?? config('firebase.project_id')
                ?? 'gonulkoprusu-325eb'
            ),
            'credentials_path' => $path,
            'credentials_source' => $this->credentialsSource ?? 'none',
            'device_tokens_table' => $this->deviceTokensTableExists(),
            'device_count' => $this->registeredDeviceCount(),
            'openssl' => extension_loaded('openssl'),
            'hints' => $hints,
        ];
    }

    public function deviceTokensTableExists(): bool
    {
        try {
            return Schema::hasTable('device_tokens');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Service account JSON kaydet (admin + web kardeş yollara kopyalar).
     *
     * @return array{ok: bool, path: ?string, mirrored: list<string>, error: ?string}
     */
    public function installCredentialsJson(string $json): array
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)
            || empty($decoded['private_key'])
            || empty($decoded['client_email'])
            || ($decoded['type'] ?? '') !== 'service_account'
        ) {
            return [
                'ok' => false,
                'path' => null,
                'mirrored' => [],
                'error' => 'Geçersiz service account JSON (type/client_email/private_key gerekli).',
            ];
        }

        $projectId = (string) ($decoded['project_id'] ?? config('firebase.project_id', 'gonulkoprusu-325eb'));
        $filename = $projectId !== '' ? $projectId.'.json' : 'gonulkoprusu-325eb.json';
        $primary = storage_path('app/firebase/'.$filename);
        $targets = array_unique(array_filter([
            $primary,
            storage_path('app/firebase/gonulkoprusu-325eb.json'),
            '/home/gonulkop/public_html/storage/app/firebase/gonulkoprusu-325eb.json',
            '/home/gonulkop/admin.gonulkoprusu.com/storage/app/firebase/gonulkoprusu-325eb.json',
        ]));

        $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (! is_string($pretty) || $pretty === '') {
            return [
                'ok' => false,
                'path' => null,
                'mirrored' => [],
                'error' => 'JSON encode başarısız.',
            ];
        }

        $mirrored = [];
        $written = null;

        foreach ($targets as $target) {
            $dir = dirname($target);
            if (! is_dir($dir) && ! @mkdir($dir, 0750, true) && ! is_dir($dir)) {
                continue;
            }
            if (@file_put_contents($target, $pretty."\n") === false) {
                continue;
            }
            @chmod($target, 0640);
            $mirrored[] = $target;
            $written ??= $target;
        }

        if ($written === null) {
            return [
                'ok' => false,
                'path' => null,
                'mirrored' => [],
                'error' => 'Dosya yazılamadı — storage/app/firebase izinlerini kontrol edin.',
            ];
        }

        $this->credentials = null;
        $this->credentialsSource = null;

        return [
            'ok' => $this->isConfigured(),
            'path' => $written,
            'mirrored' => $mirrored,
            'error' => $this->isConfigured() ? null : 'Yazıldı ancak okunamadı.',
        ];
    }

    public function registerToken(User $user, string $token, string $platform = 'android'): void
    {
        if (!$this->deviceTokensTableExists()) {
            return;
        }

        $token = trim($token);
        if ($token === '') {
            return;
        }

        DeviceToken::updateOrCreate(
            ['user_id' => $user->id, 'token' => $token],
            [
                'platform' => $platform !== '' ? $platform : 'android',
                'last_used_at' => now(),
            ]
        );
    }

    public function removeToken(User $user, ?string $token = null): void
    {
        if (!$this->deviceTokensTableExists()) {
            return;
        }

        $query = DeviceToken::where('user_id', $user->id);

        if ($token !== null) {
            $token = trim($token);
            if ($token === '') {
                return;
            }
            $query->where('token', $token);
        }

        $query->delete();
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        return $this->sendToUserId($user->id, $title, $body, $data);
    }

    public function sendToUserId(int $userId, string $title, string $body, array $data = []): int
    {
        if (!$this->isConfigured() || !$this->deviceTokensTableExists()) {
            return 0;
        }

        $tokens = DeviceToken::where('user_id', $userId)->pluck('token');

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * @param  iterable<int>  $userIds
     */
    public function sendToUserIds(iterable $userIds, string $title, string $body, array $data = []): int
    {
        if (!$this->isConfigured() || !$this->deviceTokensTableExists()) {
            return 0;
        }

        $ids = collect($userIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return 0;
        }

        $tokens = DeviceToken::whereIn('user_id', $ids)->pluck('token');

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendBroadcastPushChunked(AdminBroadcast $broadcast, int $chunkSize = 50): int
    {
        $query = User::where('role', 'user');
        if ($broadcast->target_gender !== 'all') {
            $query->where('gender', $broadcast->target_gender);
        }

        $sent = 0;
        $query->orderBy('id')->chunkById($chunkSize, function ($users) use ($broadcast, &$sent) {
            $sent += $this->sendToUserIds(
                $users->pluck('id'),
                $broadcast->title,
                $broadcast->message_text,
                [
                    'type' => 'broadcast',
                    'broadcast_id' => (string) $broadcast->id,
                ]
            );
        });

        return $sent;
    }

    public function sendBroadcastPush(AdminBroadcast $broadcast): int
    {
        return $this->sendBroadcastPushChunked($broadcast);
    }

    public function registeredDeviceCount(): int
    {
        if (!$this->deviceTokensTableExists()) {
            return 0;
        }

        try {
            return DeviceToken::count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @param  Collection<int, string>|iterable<int, string>  $tokens
     */
    private function sendToTokens($tokens, string $title, string $body, array $data): int
    {
        $sent = 0;

        foreach (collect($tokens)->unique()->filter() as $token) {
            if ($this->sendToToken((string) $token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function sendToToken(string $token, string $title, string $body, array $data): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $creds = $this->loadCredentials();
        $projectId = (string) (
            ($creds['project_id'] ?? null)
            ?: config('firebase.project_id')
            ?: 'gonulkoprusu-325eb'
        );
        $channelId = ($data['type'] ?? '') === 'new_message' ? 'gonul_messages' : 'gonul_alerts';

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $this->stringifyData($data),
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => $channelId,
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(10)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

            if ($response->successful()) {
                return true;
            }

            $status = (string) $response->json('error.status', '');
            if (in_array($status, ['NOT_FOUND', 'INVALID_ARGUMENT', 'UNREGISTERED'], true)) {
                DeviceToken::where('token', $token)->delete();
            }

            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    private function stringifyData(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $out[(string) $key] = (string) $value;
        }

        if (!isset($out['title'])) {
            $out['title'] = '';
        }
        if (!isset($out['body'])) {
            $out['body'] = '';
        }

        return $out;
    }

    private function getAccessToken(): ?string
    {
        $cacheKey = 'fcm_access_token_v1';

        try {
            $cached = Cache::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        } catch (\Throwable) {
            // Cache yoksa devam et.
        }

        $creds = $this->loadCredentials();
        if (!$creds) {
            return null;
        }

        $jwt = $this->createJwt($creds);
        if (!$jwt) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $token = $response->json('access_token');
            $expiresIn = (int) $response->json('expires_in', 3600);

            if (!is_string($token) || $token === '') {
                return null;
            }

            try {
                Cache::put($cacheKey, $token, max(60, $expiresIn - 120));
            } catch (\Throwable) {
                //
            }

            return $token;
        } catch (\Throwable) {
            return null;
        }
    }

    private function createJwt(array $creds): ?string
    {
        $email = (string) ($creds['client_email'] ?? '');
        $privateKey = (string) ($creds['private_key'] ?? '');

        if ($email === '' || $privateKey === '') {
            return null;
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $email,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $input = $header.'.'.$claims;
        $signature = '';

        if (!openssl_sign($input, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return null;
        }

        return $input.'.'.$this->base64UrlEncode($signature, true);
    }

    private function base64UrlEncode(string $data, bool $raw = false): string
    {
        $encoded = base64_encode($raw ? $data : $data);

        return rtrim(strtr($encoded, '+/', '-_'), '=');
    }

    private function resolveCredentialsPath(): ?string
    {
        $candidates = [];
        $primary = config('firebase.credentials');
        if (is_string($primary) && $primary !== '') {
            $candidates[] = $primary;
        }

        $fallbacks = config('firebase.credential_fallbacks', []);
        if (is_array($fallbacks)) {
            foreach ($fallbacks as $fallback) {
                if (is_string($fallback) && $fallback !== '') {
                    $candidates[] = $fallback;
                }
            }
        }

        foreach (array_unique($candidates) as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return is_string($primary) && $primary !== '' ? $primary : null;
    }

    private function loadCredentials(): ?array
    {
        if ($this->credentials !== null) {
            return $this->credentials ?: null;
        }

        $inline = env('FIREBASE_CREDENTIALS_JSON');
        if (is_string($inline) && trim($inline) !== '') {
            $json = json_decode($inline, true);
            if (is_array($json) && ! empty($json['private_key']) && ! empty($json['client_email'])) {
                $this->credentials = $json;
                $this->credentialsSource = 'env:FIREBASE_CREDENTIALS_JSON';

                return $json;
            }
        }

        $path = $this->resolveCredentialsPath();
        if (! is_string($path) || ! is_readable($path)) {
            $this->credentials = [];
            $this->credentialsSource = 'none';

            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);
        if (! is_array($json) || empty($json['private_key']) || empty($json['client_email'])) {
            $this->credentials = [];
            $this->credentialsSource = 'invalid:'.$path;

            return null;
        }

        $this->credentials = $json;
        $this->credentialsSource = 'file:'.$path;

        return $json;
    }
}
