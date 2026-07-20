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

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
