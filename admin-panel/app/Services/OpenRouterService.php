<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    public function isConfigured(): bool
    {
        return (string) config('services.openrouter.api_key') !== '';
    }

    public function model(): string
    {
        return (string) config('services.openrouter.model', 'openrouter/free');
    }

    /** @return array{ok: bool, message: string, latency_ms?: int} */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'message' => 'API anahtarı tanımlı değil.'];
        }

        $start = microtime(true);

        try {
            $result = $this->chat(
                'Yanıt olarak yalnızca {"status":"ok"} JSON döndür.',
                'Bağlantı testi.',
                80,
            );

            if ($result === null) {
                return ['ok' => false, 'message' => 'OpenRouter yanıt vermedi.'];
            }

            return [
                'ok' => true,
                'message' => 'Bağlantı başarılı · Model: '.$this->model(),
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        } catch (\Throwable $e) {
            Log::warning('OpenRouter test failed', ['error' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Bağlantı hatası: '.$e->getMessage()];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function chat(string $systemPrompt, string $userPrompt, int $maxTokens = 400): ?array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('API anahtarı tanımlı değil.');
        }

        $response = Http::timeout((int) config('services.openrouter.timeout', 90))
            ->retry(1, 1000)
            ->withHeaders([
                'Authorization' => 'Bearer '.config('services.openrouter.api_key'),
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])
            ->post(rtrim((string) config('services.openrouter.base_url'), '/').'/chat/completions', [
                'model' => $this->model(),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (! $response->successful()) {
            $message = data_get($response->json(), 'error.message') ?: $response->body();
            Log::warning('OpenRouter API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('OpenRouter API hatası ('.$response->status().'): '.$message);
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        $decoded = json_decode(trim($content), true);

        if (! is_array($decoded)) {
            $cleaned = trim($content);
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\s*```$/', '', $cleaned) ?? $cleaned;

            $start = strpos($cleaned, '{');
            $end = strrpos($cleaned, '}');

            if ($start !== false && $end !== false && $end > $start) {
                $decoded = json_decode(substr($cleaned, $start, $end - $start + 1), true);
            }
        }

        if (is_array($decoded)) {
            return $decoded;
        }

        return ['raw_content' => trim($content)];
    }
}
