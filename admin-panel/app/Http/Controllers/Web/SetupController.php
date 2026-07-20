<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SetupController extends Controller
{
    public function cpanel()
    {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $helper = base_path('gk_bootstrap_helpers.php');
        if (is_file($helper)) {
            require $helper;
        }

        $created = function_exists('gk_ensure_dirs') ? gk_ensure_dirs(base_path()) : [];
        $cleared = function_exists('gk_clear_bootstrap_cache') ? gk_clear_bootstrap_cache(base_path()) : [];

        foreach (glob(storage_path('framework/views/*.php')) ?: [] as $file) {
            if (@unlink($file)) {
                $cleared[] = 'views/'.basename($file);
            }
        }

        try {
            Artisan::call('package:discover', ['--ansi' => false]);
        } catch (\Throwable) {
            // ignore
        }

        try {
            Artisan::call('config:clear');
        } catch (\Throwable) {
            //
        }

        return response(
            "Gonul Koprüsü — cPanel kurulum\n".
            'base='.base_path()."\n".
            'php='.PHP_VERSION."\n\n".
            'created: '.($created ? implode(', ', $created) : '(hepsi vardı)')."\n".
            'cleared: '.($cleared ? implode(', ', $cleared) : '(yok)')."\n\n".
            "OK\n",
            200,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }

    public function messagesSchema()
    {
        if (request('key') !== 'gk-messages-migrate-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — mesaj şema migration', 'base='.base_path(), ''];

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/2024_06_15_000001_add_message_hidden_columns.php',
            ]);
            $output = trim(Artisan::output());
            $lines[] = 'migration: '.($output !== '' ? $output : 'OK');
        } catch (\Throwable $e) {
            $lines[] = 'migration HATA: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function fcm()
    {
        if (request('key') !== 'gk-fcm-setup-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — Admin FCM kurulum', 'base='.base_path(), ''];

        $firebaseDir = storage_path('app/firebase');
        if (! is_dir($firebaseDir)) {
            $lines[] = @mkdir($firebaseDir, 0750, true)
                ? 'firebase dir: oluşturuldu'
                : 'firebase dir: oluşturulamadı';
        } else {
            $lines[] = 'firebase dir: var';
        }

        $configPath = config_path('firebase.php');
        $lines[] = 'config/firebase.php: '.(is_file($configPath) ? 'var' : 'YOK');

        try {
            $fcm = app(\App\Services\FcmPushService::class);

            if (request()->isMethod('post') || request()->filled('json') || request()->filled('json_b64')) {
                $payload = (string) request('json', '');
                if ($payload === '' && request()->filled('json_b64')) {
                    $payload = (string) base64_decode((string) request('json_b64'), true);
                }
                if ($payload === '' && request()->hasFile('credentials')) {
                    $payload = (string) file_get_contents(request()->file('credentials')->getRealPath());
                }
                $install = $fcm->installCredentialsJson($payload);
                $lines[] = 'install: '.(($install['ok'] ?? false) ? 'OK' : ('HATA '.($install['error'] ?? '')));
            } else {
                // Web tarafındaki credentials varsa kopyala.
                $webCred = '/home/gonulkop/public_html/storage/app/firebase/gonulkoprusu-325eb.json';
                if (! $fcm->isConfigured() && is_readable($webCred)) {
                    $install = $fcm->installCredentialsJson((string) file_get_contents($webCred));
                    $lines[] = 'sync-from-web: '.(($install['ok'] ?? false) ? 'OK' : ('HATA '.($install['error'] ?? '')));
                }
            }

            try {
                Artisan::call('config:clear');
            } catch (\Throwable) {
                //
            }

            $status = $fcm->status();
            $lines[] = 'credentials path: '.($status['credentials_path'] ?? '(null)');
            $lines[] = 'credentials source: '.($status['credentials_source'] ?? 'none');
            $lines[] = 'FCM configured: '.($status['configured'] ? 'evet' : 'hayır');
            $lines[] = 'project_id: '.($status['project_id'] ?? '');
            $lines[] = 'Kayıtlı cihaz sayısı: '.($status['device_count'] ?? 0);
            $lines[] = 'device_tokens tablosu: '.(($status['device_tokens_table'] ?? false) ? 'var' : 'YOK');
        } catch (\Throwable $e) {
            $lines[] = 'FCM servis hatası: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        $plain = implode("\n", $lines)."\n";
        $wantsForm = request('form') === '1'
            || str_contains((string) request()->header('Accept', ''), 'text/html');

        if ($wantsForm) {
            $statusLine = collect($lines)->first(fn ($l) => str_starts_with((string) $l, 'FCM configured:'));
            $configured = is_string($statusLine) && str_contains($statusLine, 'evet');
            $statusClass = $configured ? 'ok' : 'bad';
            $escaped = e($plain);
            $html = <<<HTML
<!DOCTYPE html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>FCM JSON kurulum</title>
<style>
body{font-family:system-ui,sans-serif;max-width:44rem;margin:2rem auto;padding:0 1rem;line-height:1.45;color:#1a1a1a}
pre{background:#f4f4f5;padding:1rem;overflow:auto;border-radius:8px;font-size:.8rem;white-space:pre-wrap}
textarea{width:100%;min-height:12rem;font-family:ui-monospace,monospace;font-size:.8rem;padding:.75rem;border:1px solid #ccc;border-radius:8px}
.ok{color:#0a7a32}.bad{color:#b42318}button{margin-top:.75rem;padding:.6rem 1rem;border:0;border-radius:8px;background:#1d4ed8;color:#fff;font-weight:600;cursor:pointer}
</style></head><body>
<h1>FCM service account JSON</h1>
<p class="{$statusClass}"><pre>{$escaped}</pre></p>
<form method="post" action="?key=gk-fcm-setup-2026&amp;form=1" enctype="multipart/form-data">
<p><label>JSON dosyası<br><input type="file" name="credentials" accept=".json,application/json"></label></p>
<p><label>veya JSON yapıştır<br><textarea name="json" placeholder='{"type":"service_account",...}'></textarea></label></p>
<button type="submit">Yükle ve web+admin’e kopyala</button>
</form>
<p style="margin-top:1.5rem;color:#555;font-size:.9rem">Firebase Console → Project settings → Service accounts → Generate new private key (gonulkoprusu-325eb)</p>
</body></html>
HTML;

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=utf-8',
                'Cache-Control' => 'no-store',
            ]);
        }

        return response($plain, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function laravelUpdate()
    {
        $key = (string) config('update.setup_key', 'gk-laravel-update-2026');
        if (request('key') !== $key && request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        @set_time_limit(600);
        @ini_set('max_execution_time', '600');

        try {
            $updater = app(\App\Services\LaravelUpdateService::class);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'LaravelUpdateService yok: '.$e->getMessage(),
            ], 500);
        }

        if (request()->boolean('extract') || request('extract') === '1') {
            return response()->json($updater->extractVendorBundle());
        }

        if (request()->boolean('run') || request('run') === '1') {
            $mode = (string) request('mode', 'target');
            if (! in_array($mode, ['target', 'patch'], true)) {
                $mode = 'target';
            }
            $result = $updater->runUpdate($mode);

            return response()->json($result + [
                'current' => $updater->currentVersion(),
                'php' => $updater->phpVersion(),
                'constraint' => $updater->composerConstraint(),
            ]);
        }

        $status = $updater->localStatus();

        return response()->json($status + ['ok' => true]);
    }
}
