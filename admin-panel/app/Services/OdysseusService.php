<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OdysseusService
{
    private const CACHE_COOKIE = 'odysseus.session_cookie';

    private const CACHE_SESSION = 'odysseus.agent_session_id';

    private const CACHE_ENDPOINT = 'odysseus.model_endpoint_id';

    public function baseUrl(): string
    {
        return rtrim((string) config('services.odysseus.url', 'http://127.0.0.1:7000'), '/');
    }

    public function publicUrl(): string
    {
        $configured = trim((string) config('services.odysseus.public_url', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        return 'https://odysseus.gonulkoprusu.com';
    }

    public function workspace(): string
    {
        $configured = trim((string) config('services.odysseus.workspace', ''));
        if ($configured !== '') {
            return $configured;
        }

        return realpath(base_path('..')) ?: base_path();
    }

    /** @return array{ok: bool, message: string, status?: int} */
    public function status(): array
    {
        try {
            $response = Http::timeout(8)->get($this->baseUrl().'/');
            if ($response->successful() || $response->status() === 302 || $response->status() === 401) {
                return [
                    'ok' => true,
                    'message' => 'Odysseus çalışıyor ('.$this->baseUrl().')',
                    'status' => $response->status(),
                ];
            }

            return [
                'ok' => false,
                'message' => 'Odysseus yanıt verdi ama beklenmeyen durum: HTTP '.$response->status(),
                'status' => $response->status(),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Odysseus erişilemiyor: '.$e->getMessage().'. Sunucuda keepalive cron kontrol edin.',
            ];
        }
    }

    /**
     * Settings’te tanımlı model endpoint özeti (admin UI için).
     *
     * @return array{ok: bool, endpoints: list<array{id: string, name: string, base_url: string, is_enabled: bool, models: list<string>, status: string}>, error?: string}
     */
    public function modelEndpointsSummary(): array
    {
        $status = $this->status();
        if (! $status['ok']) {
            return ['ok' => false, 'endpoints' => [], 'error' => $status['message']];
        }

        try {
            $cookie = $this->authenticate();
            $endpoints = $this->fetchModelEndpoints($cookie);

            return [
                'ok' => true,
                'endpoints' => array_map(static function (array $ep): array {
                    $models = array_values(array_unique(array_filter(array_merge(
                        is_array($ep['pinned_models'] ?? null) ? $ep['pinned_models'] : [],
                        is_array($ep['models'] ?? null) ? $ep['models'] : [],
                    ), static fn ($m) => is_string($m) && $m !== '')));

                    return [
                        'id' => (string) ($ep['id'] ?? ''),
                        'name' => (string) ($ep['name'] ?? 'Model'),
                        'base_url' => (string) ($ep['base_url'] ?? ''),
                        'is_enabled' => (bool) ($ep['is_enabled'] ?? false),
                        'models' => array_slice($models, 0, 8),
                        'status' => (string) ($ep['status'] ?? ''),
                    ];
                }, $endpoints),
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'endpoints' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, reply: string, session_id?: string, error?: string, events?: list<string>}
     */
    public function runCommand(string $command): array
    {
        $command = trim($command);
        if ($command === '') {
            return ['ok' => false, 'reply' => '', 'error' => 'Komut boş olamaz.'];
        }

        $status = $this->status();
        if (! $status['ok']) {
            return ['ok' => false, 'reply' => '', 'error' => $status['message']];
        }

        try {
            $cookie = $this->authenticate();
            $sessionId = $this->ensureSession($cookie, forceNew: false);
            $stream = $this->chatStream($cookie, $sessionId, $command);

            // Stale endpoint / session → recreate once
            if (! $stream['ok'] && $this->isRecoverableSessionError($stream['error'] ?? '')) {
                $this->forgetCaches();
                $cookie = $this->authenticate();
                $sessionId = $this->ensureSession($cookie, forceNew: true);
                $stream = $this->chatStream($cookie, $sessionId, $command);
            }

            return [
                'ok' => $stream['ok'],
                'reply' => $stream['reply'],
                'session_id' => $sessionId,
                'error' => $stream['error'] ?? null,
                'events' => $stream['events'] ?? [],
            ];
        } catch (Throwable $e) {
            Log::warning('Odysseus command failed', ['error' => $e->getMessage()]);
            $this->forgetCaches();

            return [
                'ok' => false,
                'reply' => '',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function forgetCaches(): void
    {
        Cache::forget(self::CACHE_COOKIE);
        Cache::forget(self::CACHE_SESSION);
        Cache::forget(self::CACHE_ENDPOINT);
    }

    private function isRecoverableSessionError(string $error): bool
    {
        $error = strtolower($error);

        return str_contains($error, 'model endpoint was removed')
            || str_contains($error, 'model endpoint no longer exists')
            || str_contains($error, 'no model selected')
            || str_contains($error, 'no model endpoint')
            || str_contains($error, 'session')
            || str_contains($error, 'http 400')
            || str_contains($error, 'http 401')
            || str_contains($error, 'http 403')
            || str_contains($error, 'http 404');
    }

    private function authenticate(): string
    {
        $cached = Cache::get(self::CACHE_COOKIE);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $user = (string) config('services.odysseus.user', 'admin');
        $password = (string) config('services.odysseus.password', '');

        if ($password === '') {
            throw new \RuntimeException('ODYSSEUS_PASSWORD tanımlı değil (admin .env).');
        }

        $response = Http::timeout(20)
            ->acceptJson()
            ->post($this->baseUrl().'/api/auth/login', [
                'username' => $user,
                'password' => $password,
                'remember' => true,
            ]);

        if (! $response->successful() || ! ($response->json('ok') ?? false)) {
            $detail = $response->json('detail') ?? $response->body();
            throw new \RuntimeException('Odysseus giriş başarısız: '.(is_string($detail) ? $detail : json_encode($detail)));
        }

        $cookie = $this->extractSessionCookie($response->cookies());
        if ($cookie === '') {
            throw new \RuntimeException('Odysseus oturum çerezi alınamadı.');
        }

        Cache::put(self::CACHE_COOKIE, $cookie, now()->addHours(12));

        return $cookie;
    }

    private function extractSessionCookie($cookieJar): string
    {
        foreach ($cookieJar as $cookie) {
            $name = method_exists($cookie, 'getName') ? $cookie->getName() : (string) ($cookie['Name'] ?? '');
            $value = method_exists($cookie, 'getValue') ? $cookie->getValue() : (string) ($cookie['Value'] ?? '');
            if ($name !== '' && $value !== '' && str_contains(strtolower($name), 'session')) {
                return $name.'='.$value;
            }
        }

        foreach ($cookieJar as $cookie) {
            $name = method_exists($cookie, 'getName') ? $cookie->getName() : (string) ($cookie['Name'] ?? '');
            $value = method_exists($cookie, 'getValue') ? $cookie->getValue() : (string) ($cookie['Value'] ?? '');
            if ($name !== '' && $value !== '') {
                return $name.'='.$value;
            }
        }

        return '';
    }

    /**
     * Odysseus Settings’te kayıtlı endpoint’leri listeler (admin .env API key oluşturmaz).
     *
     * @return list<array<string, mixed>>
     */
    private function fetchModelEndpoints(string $cookieHeader): array
    {
        $response = Http::timeout(30)
            ->withHeaders(['Cookie' => $cookieHeader])
            ->acceptJson()
            ->get($this->baseUrl().'/api/model-endpoints');

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Odysseus model listesi alınamadı: HTTP '.$response->status().' '.$response->body()
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            return [];
        }

        // API düz liste veya {data:[...]} dönebilir
        if (array_is_list($json)) {
            return $json;
        }

        $data = $json['data'] ?? $json['endpoints'] ?? [];

        return is_array($data) ? $data : [];
    }

    /**
     * @return array{id: string, model: string}
     */
    private function resolveModelEndpoint(string $cookieHeader): array
    {
        $cached = Cache::get(self::CACHE_ENDPOINT);
        $preferredId = trim((string) config('services.odysseus.endpoint_id', ''));
        $preferredModel = trim((string) config('services.odysseus.model', ''));

        $endpoints = $this->fetchModelEndpoints($cookieHeader);
        $usable = [];
        foreach ($endpoints as $ep) {
            if (! is_array($ep)) {
                continue;
            }
            $id = trim((string) ($ep['id'] ?? ''));
            $base = strtolower((string) ($ep['base_url'] ?? ''));
            $enabled = (bool) ($ep['is_enabled'] ?? false);
            if ($id === '' || ! $enabled) {
                continue;
            }
            if (str_contains($base, 'openrouter.ai')) {
                continue;
            }
            $usable[] = $ep;
        }

        if ($usable === []) {
            throw new \RuntimeException(
                'Odysseus Settings’te kullanılabilir model yok. Odysseus arayüzünden Settings → Model ekleyin (OpenAI / Groq / Gemini vb.).'
            );
        }

        $selected = null;
        if ($preferredId !== '') {
            foreach ($usable as $ep) {
                if ((string) ($ep['id'] ?? '') === $preferredId) {
                    $selected = $ep;
                    break;
                }
            }
        }

        if ($selected === null && is_string($cached) && $cached !== '') {
            foreach ($usable as $ep) {
                if ((string) ($ep['id'] ?? '') === $cached) {
                    $selected = $ep;
                    break;
                }
            }
        }

        if ($selected === null && $preferredModel !== '') {
            foreach ($usable as $ep) {
                $models = array_merge(
                    is_array($ep['pinned_models'] ?? null) ? $ep['pinned_models'] : [],
                    is_array($ep['models'] ?? null) ? $ep['models'] : [],
                );
                foreach ($models as $m) {
                    if (is_string($m) && strcasecmp($m, $preferredModel) === 0) {
                        $selected = $ep;
                        break 2;
                    }
                }
            }
        }

        $selected ??= $usable[0];
        $endpointId = (string) $selected['id'];
        $model = $preferredModel;

        if ($model === '') {
            $pinned = is_array($selected['pinned_models'] ?? null) ? $selected['pinned_models'] : [];
            $models = is_array($selected['models'] ?? null) ? $selected['models'] : [];
            $model = (string) ($pinned[0] ?? $models[0] ?? '');
        }

        Cache::put(self::CACHE_ENDPOINT, $endpointId, now()->addHours(12));

        return ['id' => $endpointId, 'model' => $model];
    }

    private function ensureSession(string $cookieHeader, bool $forceNew = false): string
    {
        if (! $forceNew) {
            $cached = Cache::get(self::CACHE_SESSION);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $resolved = $this->resolveModelEndpoint($cookieHeader);

        $multipart = [
            ['name' => 'name', 'contents' => 'Admin Komut'],
            ['name' => 'endpoint_id', 'contents' => $resolved['id']],
            ['name' => 'skip_validation', 'contents' => 'false'],
        ];
        if ($resolved['model'] !== '') {
            $multipart[] = ['name' => 'model', 'contents' => $resolved['model']];
        }

        $response = Http::timeout(60)
            ->withHeaders(['Cookie' => $cookieHeader])
            ->asMultipart()
            ->post($this->baseUrl().'/api/session', $multipart);

        if (! $response->successful()) {
            Cache::forget(self::CACHE_ENDPOINT);
            throw new \RuntimeException('Odysseus oturum açılamadı: HTTP '.$response->status().' '.$response->body());
        }

        $sessionId = (string) ($response->json('id') ?? $response->json('session_id') ?? '');
        if ($sessionId === '') {
            throw new \RuntimeException('Odysseus oturum ID dönmedi.');
        }

        Cache::put(self::CACHE_SESSION, $sessionId, now()->addHours(12));

        return $sessionId;
    }

    /**
     * @return array{ok: bool, reply: string, error?: string, events?: list<string>}
     */
    private function chatStream(string $cookieHeader, string $sessionId, string $command): array
    {
        $workspace = $this->workspace();
        $prompt = $this->buildPrompt($command, $workspace);
        $timeout = (int) config('services.odysseus.timeout', 300);

        $response = Http::timeout($timeout)
            ->withHeaders([
                'Cookie' => $cookieHeader,
                'Accept' => 'text/event-stream',
            ])
            ->asMultipart()
            ->post($this->baseUrl().'/api/chat_stream', [
                ['name' => 'message', 'contents' => $prompt],
                ['name' => 'session', 'contents' => $sessionId],
                ['name' => 'mode', 'contents' => 'agent'],
                ['name' => 'workspace', 'contents' => $workspace],
                ['name' => 'allow_bash', 'contents' => 'true'],
            ]);

        if (! $response->successful()) {
            if (in_array($response->status(), [400, 401, 403, 404], true)) {
                $this->forgetCaches();
            }

            return [
                'ok' => false,
                'reply' => '',
                'error' => 'Odysseus komut hatası: HTTP '.$response->status().' '.$response->body(),
            ];
        }

        return $this->parseSse($response->body());
    }

    private function buildPrompt(string $command, string $workspace): string
    {
        return <<<PROMPT
Sen Gönül Köprüsü kod ajanısın. Workspace: {$workspace}

Kurallar:
- İstenen değişikliği doğrudan uygula (dosya yaz / düzenle).
- Mevcut tasarım ve Laravel yapısına uy.
- Gereksiz refactor yapma.
- İş bitince kısa Türkçe özet ver: hangi dosyalar değişti.

Komut:
{$command}
PROMPT;
    }

    /**
     * @return array{ok: bool, reply: string, error?: string, events?: list<string>}
     */
    private function parseSse(string $body): array
    {
        $replyParts = [];
        $events = [];
        $error = null;

        foreach (preg_split("/\r\n|\n|\r/", $body) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || ! str_starts_with($line, 'data:')) {
                continue;
            }

            $payload = trim(substr($line, 5));
            if ($payload === '' || $payload === '[DONE]') {
                continue;
            }

            $json = json_decode($payload, true);
            if (! is_array($json)) {
                continue;
            }

            $type = (string) ($json['type'] ?? $json['event'] ?? '');
            if ($type !== '') {
                $events[] = $type;
            }

            if (isset($json['delta']) && is_string($json['delta'])) {
                $replyParts[] = $json['delta'];
            } elseif (isset($json['content']) && is_string($json['content'])) {
                $replyParts[] = $json['content'];
            } elseif (isset($json['text']) && is_string($json['text'])) {
                $replyParts[] = $json['text'];
            } elseif (isset($json['response']) && is_string($json['response'])) {
                $replyParts[] = $json['response'];
            }

            if (isset($json['error']) && is_string($json['error']) && $json['error'] !== '') {
                $error = $json['error'];
            }
        }

        $reply = trim(implode('', $replyParts));
        if ($reply === '' && $body !== '') {
            $json = json_decode($body, true);
            if (is_array($json)) {
                $reply = (string) ($json['response'] ?? $json['reply'] ?? $json['message'] ?? '');
                $error = $error ?? (isset($json['error']) ? (string) $json['error'] : null);
            }
        }

        if ($error) {
            return ['ok' => false, 'reply' => $reply, 'error' => $error, 'events' => array_values(array_unique($events))];
        }

        if ($reply === '') {
            return [
                'ok' => false,
                'reply' => '',
                'error' => 'Odysseus boş yanıt döndü. Odysseus Settings → Model ayarını kontrol et.',
                'events' => array_values(array_unique($events)),
            ];
        }

        return [
            'ok' => true,
            'reply' => $reply,
            'events' => array_values(array_unique($events)),
        ];
    }
}
