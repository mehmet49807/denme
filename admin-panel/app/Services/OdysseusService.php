<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OdysseusService
{
    public function baseUrl(): string
    {
        return rtrim((string) config('services.odysseus.url', 'http://127.0.0.1:7000'), '/');
    }

    public function workspace(): string
    {
        $configured = trim((string) config('services.odysseus.workspace', ''));
        if ($configured !== '') {
            return $configured;
        }

        // Repo root: admin-panel/app/Services → ../../../
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
                'message' => 'Odysseus erişilemiyor: '.$e->getMessage().'. Sunucuda: bash scripts/odysseus/start.sh',
            ];
        }
    }

    /**
     * Run an admin command in Odysseus agent mode against the code workspace.
     *
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
            $sessionId = $this->ensureSession($cookie);
            $stream = $this->chatStream($cookie, $sessionId, $command);

            return [
                'ok' => $stream['ok'],
                'reply' => $stream['reply'],
                'session_id' => $sessionId,
                'error' => $stream['error'] ?? null,
                'events' => $stream['events'] ?? [],
            ];
        } catch (Throwable $e) {
            Log::warning('Odysseus command failed', ['error' => $e->getMessage()]);

            return [
                'ok' => false,
                'reply' => '',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function authenticate(): string
    {
        $cacheKey = 'odysseus.session_cookie';
        $cached = Cache::get($cacheKey);
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

        Cache::put($cacheKey, $cookie, now()->addHours(12));

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

        // Fallback: first cookie
        foreach ($cookieJar as $cookie) {
            $name = method_exists($cookie, 'getName') ? $cookie->getName() : (string) ($cookie['Name'] ?? '');
            $value = method_exists($cookie, 'getValue') ? $cookie->getValue() : (string) ($cookie['Value'] ?? '');
            if ($name !== '' && $value !== '') {
                return $name.'='.$value;
            }
        }

        return '';
    }

    private function ensureSession(string $cookieHeader): string
    {
        $cacheKey = 'odysseus.agent_session_id';
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $endpointUrl = trim((string) config('services.odysseus.endpoint_url', ''));
        $model = trim((string) config('services.odysseus.model', ''));
        $apiKey = trim((string) config('services.odysseus.api_key', ''));

        $multipart = [
            ['name' => 'name', 'contents' => 'Admin Komut'],
            ['name' => 'skip_validation', 'contents' => $endpointUrl === '' ? 'true' : 'false'],
        ];

        if ($endpointUrl !== '') {
            $multipart[] = ['name' => 'endpoint_url', 'contents' => $endpointUrl];
        }
        if ($model !== '') {
            $multipart[] = ['name' => 'model', 'contents' => $model];
        }
        if ($apiKey !== '') {
            $multipart[] = ['name' => 'api_key', 'contents' => $apiKey];
        }

        $response = Http::timeout(45)
            ->withHeaders(['Cookie' => $cookieHeader])
            ->asMultipart()
            ->post($this->baseUrl().'/api/session', $multipart);

        if (! $response->successful()) {
            throw new \RuntimeException('Odysseus oturum açılamadı: HTTP '.$response->status().' '.$response->body());
        }

        $sessionId = (string) ($response->json('id') ?? $response->json('session_id') ?? '');
        if ($sessionId === '') {
            throw new \RuntimeException('Odysseus oturum ID dönmedi. Odysseus Settings içinde model endpoint tanımlayın.');
        }

        Cache::put($cacheKey, $sessionId, now()->addHours(12));

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
            // Clear stale auth/session on auth errors
            if (in_array($response->status(), [401, 403, 404], true)) {
                Cache::forget('odysseus.session_cookie');
                Cache::forget('odysseus.agent_session_id');
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
            // Non-SSE JSON fallback
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
                'error' => 'Odysseus boş yanıt döndü. Settings içinde model/endpoint tanımlı mı kontrol et.',
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
