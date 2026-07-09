<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class DeployGithubService
{
    public function recordDeploy(string $target, ?string $commit = null): void
    {
        Cache::put('deploy.last_success_at', now()->timezone('Europe/Istanbul')->format('d.m.Y H:i'), now()->addDays(30));
        Cache::put('deploy.last_target', $target, now()->addDays(30));
        if ($commit) {
            Cache::put('deploy.last_commit', substr($commit, 0, 12), now()->addDays(30));
        }
    }

    /**
     * @return array{ok: bool, status: int|null, message: string, ms: int|null}
     */
    public function pingUrl(string $url, int $timeout = 12): array
    {
        $started = microtime(true);

        try {
            $response = Http::timeout($timeout)
                ->withOptions(['allow_redirects' => false])
                ->get($url);

            $ms = (int) round((microtime(true) - $started) * 1000);
            $status = $response->status();
            $ok = $status >= 200 && $status < 400;

            return [
                'ok' => $ok,
                'status' => $status,
                'message' => $ok ? 'Erişilebilir' : 'HTTP '.$status,
                'ms' => $ms,
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'status' => null,
                'message' => $e->getMessage(),
                'ms' => null,
            ];
        }
    }

    /**
     * @return array<string, array{ok: bool, status: int|null, message: string, ms: int|null}>
     */
    public function runHealthChecks(): array
    {
        $webUrl = rtrim((string) config('deploy.web_url'), '/');
        $adminUrl = rtrim((string) config('deploy.admin_url'), '/');
        $key = (string) config('deploy.setup_key');

        return [
            'web' => $this->pingUrl($webUrl),
            'admin' => $this->pingUrl($adminUrl),
            'admin_github' => $this->pingUrl($adminUrl.'/github'),
            'web_cache' => $this->pingUrl($webUrl.'/setup/clear-cache?key='.$key, 20),
            'admin_cache' => $this->pingUrl($adminUrl.'/setup/cpanel?key='.$key, 20),
        ];
    }

    /**
     * @return array{overall: string, message: string, checks: list<array{id: string, label: string, status: string, message: string}>}
     */
    public function formattedHealthChecks(): array
    {
        $labels = [
            'web' => 'Web sitesi',
            'admin' => 'Admin panel',
            'admin_github' => 'GitHub sayfası (/github)',
            'web_cache' => 'Web önbellek endpoint',
            'admin_cache' => 'Admin önbellek endpoint',
        ];

        $checks = [];
        $hasError = false;
        $hasWarning = false;

        foreach ($this->runHealthChecks() as $id => $result) {
            $status = 'error';
            if ($result['ok']) {
                $status = 'ok';
            } elseif (in_array($result['status'], [301, 302, 401, 403], true)) {
                $status = 'warning';
            }

            if ($status === 'error') {
                $hasError = true;
            }
            if ($status === 'warning') {
                $hasWarning = true;
            }

            $message = $result['message'];
            if ($result['ms'] !== null) {
                $message .= ' ('.$result['ms'].' ms)';
            }
            if ($result['status'] !== null) {
                $message .= ' · HTTP '.$result['status'];
            }

            $checks[] = [
                'id' => $id,
                'label' => $labels[$id] ?? $id,
                'status' => $status,
                'message' => $message,
            ];
        }

        $overall = $hasError ? 'error' : ($hasWarning ? 'warning' : 'ok');

        return [
            'overall' => $overall,
            'message' => match ($overall) {
                'ok' => 'Tüm kontroller başarılı',
                'warning' => 'Bazı uyarılar var (ör. login yönlendirmesi normal olabilir)',
                default => 'Hata tespit edildi — FTP veya DNS kontrol edin',
            },
            'checks' => $checks,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function latestWorkflowRun(): array
    {
        $slug = (string) config('deploy.repo_slug');
        $workflow = (string) config('deploy.workflow_file');

        try {
            $response = Http::timeout(12)
                ->accept('application/vnd.github+json')
                ->get("https://api.github.com/repos/{$slug}/actions/workflows/{$workflow}/runs", [
                    'per_page' => 1,
                ]);

            if (! $response->successful()) {
                return [
                    'found' => false,
                    'message' => 'GitHub API: HTTP '.$response->status(),
                ];
            }

            $run = $response->json('workflow_runs.0');
            if (! is_array($run)) {
                return [
                    'found' => false,
                    'message' => 'Henüz workflow çalışması yok',
                ];
            }

            $conclusion = $run['conclusion'] ?? null;
            $status = $run['status'] ?? null;

            return [
                'found' => true,
                'conclusion' => $conclusion,
                'status' => $conclusion ?: $status,
                'html_url' => $run['html_url'] ?? (string) config('deploy.actions_url'),
                'head_sha' => $run['head_sha'] ?? null,
                'created_at' => $run['created_at'] ?? null,
                'message' => $conclusion === 'success'
                    ? 'Son deploy başarılı'
                    : ($status === 'in_progress' ? 'Deploy devam ediyor' : 'Sonuç: '.($conclusion ?: $status)),
            ];
        } catch (Throwable $e) {
            return [
                'found' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, string>
     */
    public function flattenDeployPaths(): array
    {
        $paths = config('deploy.paths', []);
        $flat = [];

        foreach ($paths as $key => $items) {
            $flat[$key] = is_array($items) ? implode(' · ', $items) : (string) $items;
        }

        return $flat;
    }

    public function clearLocalCache(): void
    {
        foreach (['view:clear', 'route:clear', 'config:clear', 'cache:clear'] as $command) {
            try {
                Artisan::call($command);
            } catch (Throwable) {
                // Hosting kısıtlarında bazı komutlar başarısız olabilir.
            }
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function clearRemoteCache(string $target): array
    {
        $key = (string) config('deploy.setup_key');
        $webUrl = rtrim((string) config('deploy.web_url'), '/');
        $adminUrl = rtrim((string) config('deploy.admin_url'), '/');
        $messages = [];

        if (in_array($target, ['web', 'all'], true)) {
            $result = $this->pingUrl($webUrl.'/setup/clear-cache?key='.$key, 25);
            $messages[] = 'Web: '.($result['ok'] ? 'önbellek temizlendi' : $result['message']);
        }

        if (in_array($target, ['admin', 'all'], true)) {
            $this->clearLocalCache();
            $result = $this->pingUrl($adminUrl.'/setup/cpanel?key='.$key, 25);
            $messages[] = 'Admin: '.($result['ok'] ? 'önbellek temizlendi' : $result['message']);
        }

        $combined = implode(' · ', $messages);
        $ok = $messages !== [] && ! preg_match('/HTTP|cURL|timed out|Connection/i', $combined);

        return [
            'ok' => $ok,
            'message' => $combined !== '' ? $combined : 'İşlem tamamlandı',
        ];
    }
}
