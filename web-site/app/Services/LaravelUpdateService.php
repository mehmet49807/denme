<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LaravelUpdateService
{
    public function currentVersion(): string
    {
        return app()->version();
    }

    public function phpVersion(): string
    {
        return PHP_VERSION;
    }

    public function composerConstraint(): ?string
    {
        $json = $this->readComposerJson();
        if (! is_array($json)) {
            return null;
        }

        $require = $json['require']['laravel/framework'] ?? null;

        return is_string($require) ? $require : null;
    }

    /**
     * @return array{ok: bool, latest11: ?string, latest12: ?string, latest13: ?string, recommended: ?string, error: ?string}
     */
    public function packagistLatest(): array
    {
        $cacheKey = 'laravel_packagist_latest_v1';
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['ok'] ?? false)) {
            return $cached;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(12)
                ->get((string) config('update.packagist_url'));

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'latest11' => null,
                    'latest12' => null,
                    'latest13' => null,
                    'recommended' => null,
                    'error' => 'Packagist yanıt vermedi ('.$response->status().')',
                ];
            }

            $versions = array_keys($response->json('package.versions') ?? []);
            $latest11 = $this->highestStable($versions, 11);
            $latest12 = $this->highestStable($versions, 12);
            $latest13 = $this->highestStable($versions, 13);

            $phpMajorMinor = (float) sprintf('%d.%d', PHP_MAJOR_VERSION, PHP_MINOR_VERSION);
            $recommended = null;
            if ($phpMajorMinor >= 8.3 && $latest13) {
                $recommended = $latest13;
            } elseif ($phpMajorMinor >= 8.2 && $latest12) {
                $recommended = $latest12;
            } elseif ($latest11) {
                $recommended = $latest11;
            }

            $payload = [
                'ok' => true,
                'latest11' => $latest11,
                'latest12' => $latest12,
                'latest13' => $latest13,
                'recommended' => $recommended,
                'error' => null,
            ];

            Cache::put($cacheKey, $payload, 1800);

            return $payload;
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'latest11' => null,
                'latest12' => null,
                'latest13' => null,
                'recommended' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{current: string, php: string, constraint: ?string, target_constraint: string, target_major: int, update_available: bool, packagist: array, composer_available: bool, shell_exec: bool, base_path: string}
     */
    public function localStatus(): array
    {
        $packagist = $this->packagistLatest();
        $current = $this->currentVersion();
        $targetMajor = (int) config('update.target_major', 12);
        $recommended = $packagist['recommended'] ?? null;

        $updateAvailable = false;
        if (is_string($recommended) && preg_match('/^(\d+)\.(\d+)\.(\d+)/', ltrim($recommended, 'v'), $m)) {
            $updateAvailable = version_compare($current, "{$m[1]}.{$m[2]}.{$m[3]}", '<');
        }

        return [
            'current' => $current,
            'php' => $this->phpVersion(),
            'constraint' => $this->composerConstraint(),
            'target_constraint' => (string) config('update.target_constraint', '^12.0'),
            'target_major' => $targetMajor,
            'update_available' => $updateAvailable,
            'packagist' => $packagist,
            'composer_available' => $this->composerBinary() !== null,
            'shell_exec' => $this->shellExecEnabled(),
            'base_path' => base_path(),
        ];
    }

    /**
     * @return array{ok: bool, message: string, before: ?string, after: ?string, output: string, target: string}
     */
    public function runUpdate(string $mode = 'target'): array
    {
        @set_time_limit(600);
        @ini_set('max_execution_time', '600');
        @ini_set('memory_limit', '512M');

        $before = $this->currentVersion();
        $targetConstraint = match ($mode) {
            'patch' => '^'.explode('.', $before)[0].'.0',
            'target' => (string) config('update.target_constraint', '^12.0'),
            default => (string) config('update.target_constraint', '^12.0'),
        };

        if (! $this->shellExecEnabled()) {
            return $this->finish(false, 'shell_exec kapalı — cPanel’de Composer CLI gerekir.', $before, null, '', $targetConstraint);
        }

        $composer = $this->composerBinary();
        if ($composer === null) {
            return $this->finish(false, 'Composer bulunamadı (composer / php composer.phar).', $before, null, '', $targetConstraint);
        }

        $composerJsonPath = base_path('composer.json');
        if (! is_file($composerJsonPath) || ! is_writable($composerJsonPath)) {
            return $this->finish(false, 'composer.json yazılamıyor.', $before, null, '', $targetConstraint);
        }

        $json = $this->readComposerJson();
        if (! is_array($json)) {
            return $this->finish(false, 'composer.json okunamadı.', $before, null, '', $targetConstraint);
        }

        $json['require']['laravel/framework'] = $targetConstraint;
        $encoded = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (! is_string($encoded) || @file_put_contents($composerJsonPath, $encoded."\n") === false) {
            return $this->finish(false, 'composer.json güncellenemedi.', $before, null, '', $targetConstraint);
        }

        $base = escapeshellarg(base_path());
        $constraint = escapeshellarg('laravel/framework:'.$targetConstraint);
        $commands = [
            "cd {$base} && {$composer} require {$constraint} --no-interaction --with-all-dependencies --no-progress 2>&1",
            "cd {$base} && {$composer} update laravel/framework --with-dependencies --no-interaction --no-progress 2>&1",
        ];

        $output = '';
        foreach ($commands as $command) {
            $chunk = @shell_exec($command);
            if (is_string($chunk) && trim($chunk) !== '') {
                $output .= trim($chunk)."\n";
            }
        }

        // OPcache / autoload yenile
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
        } catch (\Throwable) {
            //
        }

        $after = $this->readInstalledFrameworkVersion() ?? $this->currentVersion();
        $ok = version_compare($after, $before, '>')
            || (str_starts_with($targetConstraint, '^12') && version_compare($after, '12.0.0', '>='));

        $message = $ok
            ? "Laravel {$before} → {$after} güncellendi ({$targetConstraint})."
            : "Güncelleme tamamlanamadı (şu an {$after}). Composer çıktısını kontrol edin.";

        return $this->finish($ok, $message, $before, $after, $output, $targetConstraint);
    }

    /**
     * Uzak hedefe (web/admin) güncelleme isteği gönder.
     *
     * @return array{ok: bool, message: string, remote: ?array}
     */
    public function runRemoteUpdate(string $target, string $mode = 'target'): array
    {
        $base = match ($target) {
            'web' => rtrim((string) config('update.web_url'), '/'),
            'admin' => rtrim((string) config('update.admin_url'), '/'),
            default => null,
        };

        if ($base === null) {
            return ['ok' => false, 'message' => 'Geçersiz hedef.', 'remote' => null];
        }

        $key = (string) config('update.setup_key');
        $url = $base.'/setup/laravel-update?key='.urlencode($key).'&run=1&mode='.urlencode($mode);

        try {
            $response = Http::timeout(580)
                ->acceptJson()
                ->get($url);

            $json = $response->json();
            if (! is_array($json)) {
                return [
                    'ok' => false,
                    'message' => 'Uzak yanıt JSON değil (HTTP '.$response->status().').',
                    'remote' => ['raw' => mb_substr($response->body(), 0, 500)],
                ];
            }

            return [
                'ok' => (bool) ($json['ok'] ?? false),
                'message' => (string) ($json['message'] ?? 'Uzak güncelleme'),
                'remote' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Uzak istek hatası: '.$e->getMessage(),
                'remote' => null,
            ];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function history(): array
    {
        $items = Cache::get('laravel_update_history', []);

        return is_array($items) ? array_values($items) : [];
    }

    public function probeRemote(string $target): array
    {
        $base = match ($target) {
            'web' => rtrim((string) config('update.web_url'), '/'),
            'admin' => rtrim((string) config('update.admin_url'), '/'),
            default => null,
        };
        if ($base === null) {
            return ['ok' => false, 'error' => 'Geçersiz hedef'];
        }

        try {
            $key = (string) config('update.setup_key');
            $response = Http::timeout(20)
                ->acceptJson()
                ->get($base.'/setup/laravel-update?key='.urlencode($key));

            $json = $response->json();
            if (! is_array($json)) {
                return ['ok' => false, 'error' => 'HTTP '.$response->status(), 'raw' => mb_substr($response->body(), 0, 200)];
            }

            return $json + ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function shellExecEnabled(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return ! in_array('shell_exec', $disabled, true);
    }

    private function composerBinary(): ?string
    {
        if (! $this->shellExecEnabled()) {
            return null;
        }

        $candidates = [
            'composer',
            'php '.escapeshellarg(base_path('composer.phar')),
            'php /usr/local/bin/composer',
            'php /opt/cpanel/composer/bin/composer',
        ];

        foreach ($candidates as $bin) {
            $check = @shell_exec($bin.' --version 2>&1');
            if (is_string($check) && stripos($check, 'Composer') !== false) {
                return $bin;
            }
        }

        // composer.phar yoksa indir (tek sefer)
        $phar = base_path('composer.phar');
        if (! is_file($phar)) {
            try {
                $installer = Http::timeout(60)->get('https://getcomposer.org/download/latest-stable/composer.phar');
                if ($installer->successful()) {
                    @file_put_contents($phar, $installer->body());
                }
            } catch (\Throwable) {
                //
            }
        }

        if (is_file($phar)) {
            $bin = 'php '.escapeshellarg($phar);
            $check = @shell_exec($bin.' --version 2>&1');
            if (is_string($check) && stripos($check, 'Composer') !== false) {
                return $bin;
            }
        }

        return null;
    }

    private function readComposerJson(): ?array
    {
        $path = base_path('composer.json');
        if (! is_readable($path)) {
            return null;
        }
        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? $json : null;
    }

    private function readInstalledFrameworkVersion(): ?string
    {
        $lock = base_path('composer.lock');
        if (is_readable($lock)) {
            $data = json_decode((string) file_get_contents($lock), true);
            if (is_array($data)) {
                foreach ($data['packages'] ?? [] as $package) {
                    if (($package['name'] ?? '') === 'laravel/framework') {
                        return ltrim((string) ($package['version'] ?? ''), 'v');
                    }
                }
            }
        }

        $versionFile = base_path('vendor/laravel/framework/src/Illuminate/Foundation/Application.php');
        if (is_readable($versionFile)) {
            $src = (string) file_get_contents($versionFile);
            if (preg_match("/const VERSION = '([^']+)'/", $src, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $versions
     */
    private function highestStable(array $versions, int $major): ?string
    {
        $best = null;
        foreach ($versions as $version) {
            if (! preg_match('/^v?(\d+)\.(\d+)\.(\d+)$/', $version, $m)) {
                continue;
            }
            if ((int) $m[1] !== $major) {
                continue;
            }
            $normalized = "{$m[1]}.{$m[2]}.{$m[3]}";
            if ($best === null || version_compare($normalized, $best, '>')) {
                $best = $normalized;
            }
        }

        return $best;
    }

    /**
     * @return array{ok: bool, message: string, before: ?string, after: ?string, output: string, target: string}
     */
    private function finish(bool $ok, string $message, ?string $before, ?string $after, string $output, string $target): array
    {
        $entry = [
            'ok' => $ok,
            'message' => $message,
            'before' => $before,
            'after' => $after,
            'target' => $target,
            'at' => now()->toDateTimeString(),
            'host' => request()->getHost(),
            'output_tail' => mb_substr(trim($output), -1200),
        ];

        $history = $this->history();
        array_unshift($history, $entry);
        $limit = (int) config('update.history_limit', 20);
        Cache::put('laravel_update_history', array_slice($history, 0, $limit), now()->addDays(90));

        return [
            'ok' => $ok,
            'message' => $message,
            'before' => $before,
            'after' => $after,
            'output' => mb_substr(trim($output), -4000),
            'target' => $target,
        ];
    }
}
