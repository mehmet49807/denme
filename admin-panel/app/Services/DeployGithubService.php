<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DeployGithubService
{
    public function recordDeploy(string $target, ?string $commit = null): void
    {
        $at = now()->timezone('Europe/Istanbul')->format('d.m.Y H:i');
        $short = $commit ? substr($commit, 0, 12) : null;

        Cache::put('deploy.last_success_at', $at, now()->addDays(30));
        Cache::put('deploy.last_target', $target, now()->addDays(30));
        if ($short) {
            Cache::put('deploy.last_commit', $short, now()->addDays(30));
        }

        $history = Cache::get('deploy.success_history', []);
        if (! is_array($history)) {
            $history = [];
        }
        array_unshift($history, [
            'sha' => $short,
            'full_sha' => $commit,
            'target' => $target,
            'at' => $at,
        ]);
        Cache::put('deploy.success_history', array_slice($history, 0, 12), now()->addDays(60));

        try {
            Storage::put('deploy/version.json', json_encode([
                'sha' => $short,
                'full_sha' => $commit,
                'target' => $target,
                'deployed_at' => $at,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } catch (Throwable) {
            //
        }
    }

    public function githubToken(): string
    {
        return trim((string) config('deploy.github_token', ''));
    }

    public function hasGithubToken(): bool
    {
        return $this->githubToken() !== '';
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{ok: bool, status: int, json: mixed, body: string}
     */
    public function githubApi(string $method, string $path, array $query = [], ?array $json = null): array
    {
        $slug = (string) config('deploy.repo_slug');
        $url = str_starts_with($path, 'http')
            ? $path
            : 'https://api.github.com/repos/'.$slug.'/'.ltrim($path, '/');

        try {
            $request = Http::timeout(20)
                ->accept('application/vnd.github+json')
                ->withHeaders([
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'User-Agent' => 'GonulKoprusu-AdminDeploy',
                ]);

            if ($this->hasGithubToken()) {
                $request = $request->withToken($this->githubToken());
            }

            $response = match (strtoupper($method)) {
                'POST' => $request->post($url, $json ?? []),
                'GET' => $request->get($url, $query),
                default => $request->send($method, $url, ['query' => $query, 'json' => $json]),
            };

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'json' => $response->json(),
                'body' => $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'json' => null,
                'body' => $e->getMessage(),
            ];
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
        $runs = $this->listWorkflowRuns(1);
        if (! ($runs['ok'] ?? false) || empty($runs['runs'])) {
            return [
                'found' => false,
                'message' => $runs['message'] ?? 'Workflow bulunamadı',
            ];
        }

        $run = $runs['runs'][0];

        return array_merge($run, [
            'found' => true,
            'message' => ($run['conclusion'] ?? null) === 'success'
                ? 'Son deploy başarılı'
                : (($run['status'] ?? '') === 'in_progress' || ($run['status'] ?? '') === 'queued'
                    ? 'Deploy devam ediyor'
                    : 'Sonuç: '.($run['conclusion'] ?: $run['status'])),
        ]);
    }

    /**
     * @return array{ok: bool, message?: string, runs: list<array<string, mixed>>}
     */
    public function listWorkflowRuns(int $limit = 10): array
    {
        $workflow = (string) config('deploy.workflow_file');
        $response = $this->githubApi('GET', '/actions/workflows/'.$workflow.'/runs', [
            'per_page' => max(1, min(20, $limit)),
            'branch' => (string) config('deploy.branch', 'master'),
        ]);

        if (! $response['ok']) {
            return [
                'ok' => false,
                'message' => 'GitHub API: HTTP '.$response['status'].' · '.substr((string) $response['body'], 0, 120),
                'runs' => [],
            ];
        }

        $items = $response['json']['workflow_runs'] ?? [];
        if (! is_array($items)) {
            $items = [];
        }

        $runs = [];
        foreach ($items as $run) {
            if (! is_array($run)) {
                continue;
            }
            $conclusion = $run['conclusion'] ?? null;
            $status = $run['status'] ?? null;
            $sha = (string) ($run['head_sha'] ?? '');
            $runs[] = [
                'id' => $run['id'] ?? null,
                'conclusion' => $conclusion,
                'status' => $conclusion ?: $status,
                'event' => $run['event'] ?? null,
                'html_url' => $run['html_url'] ?? (string) config('deploy.actions_url'),
                'head_sha' => $sha,
                'sha_short' => $sha !== '' ? substr($sha, 0, 7) : null,
                'display_title' => $run['display_title'] ?? ($run['head_commit']['message'] ?? 'Deploy'),
                'created_at' => $run['created_at'] ?? null,
                'updated_at' => $run['updated_at'] ?? null,
                'actor' => $run['actor']['login'] ?? ($run['triggering_actor']['login'] ?? null),
                'run_number' => $run['run_number'] ?? null,
            ];
        }

        return ['ok' => true, 'runs' => $runs];
    }

    /**
     * @return array{ok: bool, message: string, url?: string}
     */
    public function triggerLaravelUpdate(string $target = 'all', string $mode = 'target'): array
    {
        if (! in_array($target, ['all', 'web', 'admin'], true)) {
            $target = 'all';
        }
        if (! in_array($mode, ['target', 'patch'], true)) {
            $mode = 'target';
        }

        if (! $this->hasGithubToken()) {
            return [
                'ok' => false,
                'message' => 'Laravel güncellemesi için DEPLOY_GITHUB_TOKEN (repo + actions:write) tanımlayın. Alternatif: GitHub → Actions → Laravel Update → Run workflow.',
                'url' => (string) config('deploy.laravel_update_actions_url'),
            ];
        }

        $workflow = (string) config('deploy.laravel_update_workflow', 'laravel-update.yml');
        $branch = (string) config('deploy.branch', 'master');

        $response = $this->githubApi('POST', '/actions/workflows/'.$workflow.'/dispatches', [], [
            'ref' => $branch,
            'inputs' => [
                'target' => $target,
                'mode' => $mode,
            ],
        ]);

        if ($response['status'] === 204 || $response['ok']) {
            return [
                'ok' => true,
                'message' => "Laravel Update workflow tetiklendi ({$target} · {$mode}). Vendor FTP ile yüklenecek.",
                'url' => (string) config('deploy.laravel_update_actions_url'),
            ];
        }

        return [
            'ok' => false,
            'message' => 'Laravel Update tetiklenemedi: HTTP '.$response['status'].' · '.substr((string) $response['body'], 0, 180),
            'url' => (string) config('deploy.laravel_update_actions_url'),
        ];
    }

    /**
     * @return array{ok: bool, message: string, url?: string}
     */
    public function triggerDeploy(string $target = 'all', string $syncMode = 'delta'): array
    {
        if (! in_array($target, ['all', 'web', 'admin'], true)) {
            $target = 'all';
        }
        if (! in_array($syncMode, ['delta', 'full'], true)) {
            $syncMode = 'delta';
        }

        if (! $this->hasGithubToken()) {
            return [
                'ok' => false,
                'message' => 'Deploy tetiklemek için DEPLOY_GITHUB_TOKEN (repo + actions:write) tanımlayın.',
            ];
        }

        $workflow = (string) config('deploy.workflow_file');
        $branch = (string) config('deploy.branch', 'master');

        $response = $this->githubApi('POST', '/actions/workflows/'.$workflow.'/dispatches', [], [
            'ref' => $branch,
            'inputs' => [
                'target' => $target,
                'sync_mode' => $syncMode,
            ],
        ]);

        if ($response['status'] === 204 || $response['ok']) {
            Cache::put('deploy.last_trigger_at', now()->timezone('Europe/Istanbul')->format('d.m.Y H:i'), now()->addDays(7));
            Cache::put('deploy.last_trigger_target', $target, now()->addDays(7));

            return [
                'ok' => true,
                'message' => "Deploy tetiklendi ({$target} · {$syncMode}). Actions birkaç saniye içinde başlar.",
                'url' => (string) config('deploy.actions_url'),
            ];
        }

        return [
            'ok' => false,
            'message' => 'Deploy tetiklenemedi: HTTP '.$response['status'].' · '.substr((string) $response['body'], 0, 180),
        ];
    }

    /**
     * @return array{
     *   configured: bool,
     *   token_ready: bool,
     *   mode: string,
     *   hint: string,
     *   items: list<array{name: string, present: bool|null, label: string, source: string}>
     * }
     */
    public function secretsStatus(): array
    {
        $required = array_values(config('deploy.required_secrets', []));
        $tokenReady = $this->hasGithubToken();
        $remoteNames = [];
        $apiChecked = false;

        if ($tokenReady) {
            $response = $this->githubApi('GET', '/actions/secrets', ['per_page' => 100]);
            if ($response['ok']) {
                $apiChecked = true;
                foreach (($response['json']['secrets'] ?? []) as $secret) {
                    if (is_array($secret) && ! empty($secret['name'])) {
                        $remoteNames[strtoupper((string) $secret['name'])] = true;
                    }
                }
            }
        }

        // Token yoksa: son başarılı deploy, FTP secret'larının tanımlı olduğuna güçlü işaret.
        $inferredOk = false;
        if (! $apiChecked) {
            $latest = $this->latestWorkflowRun();
            $inferredOk = ($latest['found'] ?? false) && ($latest['conclusion'] ?? null) === 'success';
        }

        $items = [];
        foreach ($required as $name) {
            $key = strtoupper((string) $name);
            $present = null;
            $source = 'unknown';
            $label = 'Bilinmiyor';

            if ($apiChecked) {
                $present = isset($remoteNames[$key]);
                $source = 'github';
                $label = $present ? 'VAR' : 'EKSİK';
            } elseif ($key === 'SETUP_CACHE_KEY') {
                $present = trim((string) config('deploy.setup_key')) !== '';
                $source = 'local';
                $label = $present ? 'VAR (sunucu)' : 'EKSİK (sunucu)';
            } elseif ($inferredOk) {
                // Deploy workflow önce check-secrets çalıştırır; success ≈ secret'lar var.
                $present = true;
                $source = 'inferred';
                $label = 'VAR (son deploy OK)';
            } else {
                $present = null;
                $source = 'needs_token';
                $label = 'Kontrol edilemedi';
            }

            $items[] = [
                'name' => $key,
                'present' => $present,
                'label' => $label,
                'source' => $source,
            ];
        }

        $hint = $apiChecked
            ? 'GitHub Secrets API ile doğrulandı (değerler gizlidir).'
            : ($tokenReady
                ? 'Token var ama Secrets API okunamadı — token yetkisini kontrol edin (repo admin).'
                : 'GitHub secret değerleri dışarıdan okunamaz. Kesin kontrol için sunucuya DEPLOY_GITHUB_TOKEN ekleyin; şimdilik son başarılı deploy’dan çıkarım yapılıyor.');

        return [
            'configured' => $tokenReady,
            'token_ready' => $tokenReady,
            'mode' => $apiChecked ? 'api' : ($inferredOk ? 'inferred' : 'unknown'),
            'hint' => $hint,
            'items' => $items,
        ];
    }

    /**
     * @return array{ok: bool, message?: string, items: list<array<string, mixed>>, count: int}
     */
    public function openPullRequests(): array
    {
        $response = $this->githubApi('GET', '/pulls', [
            'state' => 'open',
            'per_page' => 10,
            'sort' => 'updated',
            'direction' => 'desc',
        ]);

        if (! $response['ok']) {
            return [
                'ok' => false,
                'message' => 'PR listesi alınamadı (HTTP '.$response['status'].')',
                'items' => [],
                'count' => 0,
            ];
        }

        $items = [];
        foreach (($response['json'] ?? []) as $pr) {
            if (! is_array($pr)) {
                continue;
            }
            $items[] = [
                'number' => $pr['number'] ?? null,
                'title' => $pr['title'] ?? '',
                'html_url' => $pr['html_url'] ?? null,
                'user' => $pr['user']['login'] ?? null,
                'draft' => (bool) ($pr['draft'] ?? false),
                'updated_at' => $pr['updated_at'] ?? null,
                'head' => $pr['head']['ref'] ?? null,
                'base' => $pr['base']['ref'] ?? null,
            ];
        }

        return [
            'ok' => true,
            'items' => $items,
            'count' => count($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function commitSyncStatus(): array
    {
        $branch = (string) config('deploy.branch', 'master');
        $liveSha = Cache::get('deploy.last_commit');
        $liveAt = Cache::get('deploy.last_success_at');
        $liveTarget = Cache::get('deploy.last_target');

        $head = $this->githubApi('GET', '/commits/'.$branch);
        $headSha = null;
        $headMessage = null;
        $headDate = null;

        if ($head['ok'] && is_array($head['json'])) {
            $headSha = (string) ($head['json']['sha'] ?? '');
            $headMessage = (string) ($head['json']['commit']['message'] ?? '');
            $headDate = $head['json']['commit']['committer']['date'] ?? null;
        }

        $headShort = $headSha ? substr($headSha, 0, 12) : null;
        $inSync = false;
        if ($liveSha && $headSha) {
            $live = (string) $liveSha;
            $inSync = str_starts_with($headSha, $live) || str_starts_with($live, substr($headSha, 0, strlen($live)));
        }

        $compareUrl = rtrim((string) config('deploy.repository'), '/').'/compare/';
        if ($liveSha && $headShort) {
            $compareUrl .= $liveSha.'...'.$headShort;
        } else {
            $compareUrl = (string) config('deploy.compare_url');
        }

        return [
            'branch' => $branch,
            'live_sha' => $liveSha,
            'live_at' => $liveAt,
            'live_target' => $liveTarget,
            'head_sha' => $headShort,
            'head_full' => $headSha,
            'head_message' => $headMessage ? strtok($headMessage, "\n") : null,
            'head_date' => $headDate,
            'in_sync' => (bool) $inSync,
            'compare_url' => $compareUrl,
        ];
    }

    /**
     * @return array{ok: bool, overall: string, checks: list<array{id: string, label: string, ok: bool, status: int|null, message: string, ms: int|null}>}
     */
    public function runSmokeTests(): array
    {
        $webUrl = rtrim((string) config('deploy.web_url'), '/');
        $adminUrl = rtrim((string) config('deploy.admin_url'), '/');

        $targets = [
            'web_home' => ['label' => 'Web ana sayfa', 'url' => $webUrl],
            'web_login' => ['label' => 'Web login', 'url' => $webUrl.'/login'],
            'web_feed' => ['label' => 'Web feed', 'url' => $webUrl.'/feed', 'allow_redirect' => true],
            'admin_home' => ['label' => 'Admin giriş', 'url' => $adminUrl.'/login'],
            'admin_dashboard' => ['label' => 'Admin dashboard (auth)', 'url' => $adminUrl.'/dashboard', 'allow_redirect' => true],
            'web_css' => ['label' => 'Web CSS varlık', 'url' => $webUrl.'/css/app.css', 'soft' => true],
            'admin_css' => ['label' => 'Admin CSS', 'url' => $adminUrl.'/css/admin-lumiere.css'],
        ];

        $checks = [];
        $failed = 0;

        foreach ($targets as $id => $meta) {
            $started = microtime(true);
            try {
                $response = Http::timeout(15)
                    ->withOptions(['allow_redirects' => false])
                    ->get($meta['url']);
                $ms = (int) round((microtime(true) - $started) * 1000);
                $status = $response->status();
                $allowRedirect = ! empty($meta['allow_redirect']);
                $soft = ! empty($meta['soft']);
                $ok = ($status >= 200 && $status < 400)
                    || ($allowRedirect && in_array($status, [301, 302, 303, 307, 308], true))
                    || ($soft && in_array($status, [404], true) === false && $status < 500);

                if ($soft && $status === 404) {
                    $ok = true; // optional asset
                }

                if (! $ok) {
                    $failed++;
                }

                $checks[] = [
                    'id' => $id,
                    'label' => $meta['label'],
                    'ok' => $ok,
                    'status' => $status,
                    'message' => $ok ? 'OK' : 'HTTP '.$status,
                    'ms' => $ms,
                ];
            } catch (Throwable $e) {
                $failed++;
                $checks[] = [
                    'id' => $id,
                    'label' => $meta['label'],
                    'ok' => false,
                    'status' => null,
                    'message' => $e->getMessage(),
                    'ms' => null,
                ];
            }
        }

        $payload = [
            'ok' => $failed === 0,
            'overall' => $failed === 0 ? 'ok' : 'error',
            'checks' => $checks,
            'ran_at' => now()->timezone('Europe/Istanbul')->format('d.m.Y H:i:s'),
        ];

        Cache::put('deploy.last_smoke', $payload, now()->addDays(7));

        return $payload;
    }

    /**
     * @return array{active: bool, run?: array<string, mixed>, dismissed: bool}
     */
    public function failureAlert(): array
    {
        $latest = $this->latestWorkflowRun();
        $dismissedSha = Cache::get('deploy.failure_dismissed_sha');
        $sha = $latest['head_sha'] ?? null;
        $isFailure = ($latest['found'] ?? false) && ($latest['conclusion'] ?? null) === 'failure';
        $dismissed = $isFailure && $dismissedSha && $sha && hash_equals((string) $dismissedSha, (string) $sha);

        if ($isFailure && ! $dismissed) {
            $this->maybeSendFailureEmail($latest);
        }

        return [
            'active' => $isFailure && ! $dismissed,
            'dismissed' => (bool) $dismissed,
            'run' => ($latest['found'] ?? false) ? $latest : null,
        ];
    }

    public function dismissFailureAlert(?string $sha = null): void
    {
        Cache::put('deploy.failure_dismissed_sha', $sha ?: 'manual', now()->addDays(14));
    }

    /**
     * @param  array<string, mixed>  $run
     */
    private function maybeSendFailureEmail(array $run): void
    {
        $sha = (string) ($run['head_sha'] ?? '');
        $mailKey = 'deploy.failure_mailed_sha';
        if ($sha !== '' && Cache::get($mailKey) === $sha) {
            return;
        }

        $to = trim((string) config('deploy.alert_email'));
        if ($to === '') {
            return;
        }

        try {
            Mail::raw(
                "Gonul Koprusu deploy basarisiz.\n\n".
                'SHA: '.($run['sha_short'] ?? $sha)."\n".
                'Durum: '.($run['conclusion'] ?? 'failure')."\n".
                'URL: '.($run['html_url'] ?? '')."\n",
                function ($message) use ($to) {
                    $message->to($to)->subject('Deploy başarısız — Gönül Köprüsü');
                }
            );
            if ($sha !== '') {
                Cache::put($mailKey, $sha, now()->addDays(14));
            }
        } catch (Throwable $e) {
            Log::warning('Deploy failure mail skipped', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rollbackInfo(): array
    {
        $history = Cache::get('deploy.success_history', []);
        if (! is_array($history)) {
            $history = [];
        }

        $runs = $this->listWorkflowRuns(10);

        // Prefer second successful run as rollback target when latest is current
        $successRuns = array_values(array_filter(
            $runs['runs'] ?? [],
            fn ($run) => ($run['conclusion'] ?? null) === 'success'
        ));

        $current = $successRuns[0] ?? null;
        $previous = $successRuns[1] ?? ($history[1] ?? null);

        $prevSha = is_array($previous)
            ? ($previous['sha_short'] ?? $previous['sha'] ?? null)
            : null;
        $prevFull = is_array($previous)
            ? ($previous['head_sha'] ?? $previous['full_sha'] ?? null)
            : null;

        $steps = [
            'Actions üzerinde önceki başarılı çalışmayı açın.',
            'Gerekirse o commit’i master’a geri almak için revert PR açın veya FTP ile o SHA’yı yeniden deploy edin.',
            'Deploy sonrası Önbelleği Temizle + Smoke Test çalıştırın.',
        ];

        return [
            'current' => $current,
            'previous' => $previous,
            'previous_sha' => $prevSha,
            'previous_full' => $prevFull,
            'history' => array_slice($history, 0, 5),
            'compare_url' => ($prevSha && ! empty($current['sha_short']))
                ? rtrim((string) config('deploy.repository'), '/').'/compare/'.$prevSha.'...'.$current['sha_short']
                : (string) config('deploy.compare_url'),
            'rerun_url' => is_array($previous) ? ($previous['html_url'] ?? null) : null,
            'steps' => $steps,
        ];
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
                //
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
