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
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($helper, true);
            @opcache_invalidate(__FILE__, true);
        }
        require $helper;

        $created = gk_ensure_dirs(base_path());
        $cleared = gk_clear_bootstrap_cache(base_path());

        foreach (glob(storage_path('framework/views/*.php')) ?: [] as $file) {
            if (@unlink($file)) {
                $cleared[] = 'views/'.basename($file);
            }
        }

        $lines = [
            'Gonul Koprüsü — cPanel kurulum',
            'base='.base_path(),
            'php='.PHP_VERSION,
            'host='.request()->getHost(),
            'admin_mode='.(\App\Support\AdminApp::isSubdomainRequest() ? 'evet' : 'hayir'),
            '',
            'created: '.($created ? implode(', ', $created) : '(hepsi vardı)'),
            'cleared: '.($cleared ? implode(', ', $cleared) : '(yok)'),
        ];

        if (! empty($GLOBALS['gk_deploy_sync_log'])) {
            $lines[] = '';
            $lines[] = '--- deploy sync ---';
            foreach ($GLOBALS['gk_deploy_sync_log'] as $entry) {
                $lines[] = $entry;
            }
        }

        if (! empty($GLOBALS['gk_pusher_status_log'])) {
            $lines[] = '';
            foreach ($GLOBALS['gk_pusher_status_log'] as $entry) {
                $lines[] = $entry;
            }
        }

        if (in_array((string) request('d'), ['1', 'yes', 'on'], true)) {
            $lines[] = '';
            $lines[] = '--- Pusher ---';
            $lines[] = 'BROADCAST_CONNECTION='.config('broadcasting.default');
            $lines[] = 'PUSHER key: '.(config('broadcasting.connections.pusher.key') ? 'tanimli' : 'yok');
            $lines[] = 'SDK: '.(class_exists(\Pusher\Pusher::class) ? 'yuklu' : 'eksik');
            try {
                $realtime = app(\App\Services\RealtimeBroadcastService::class);
                $lines[] = 'Realtime enabled: '.($realtime->isEnabled() ? 'evet' : 'hayir');
            } catch (\Throwable $e) {
                $lines[] = 'Realtime kontrol: '.$e->getMessage();
            }
        }

        try {
            Artisan::call('package:discover', ['--ansi' => false]);
        } catch (\Throwable) {
            // ignore
        }

        if (request('deploy') === '1') {
            try {
                Artisan::call('view:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                $lines[] = 'view/config/route cache temizlendi';
            } catch (\Throwable $e) {
                $lines[] = 'cache uyarı: '.$e->getMessage();
            }

            try {
                Artisan::call('stories:purge');
                $output = trim(Artisan::output());
                $lines[] = 'stories:purge: '.($output !== '' ? $output : 'OK');
            } catch (\Throwable $e) {
                $lines[] = 'stories:purge HATA: '.$e->getMessage();
            }

            try {
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path' => 'database/migrations/2024_06_20_000001_create_password_reset_tokens_table.php',
                ]);
                $output = trim(Artisan::output());
                $lines[] = 'password_reset_tokens migration: '.($output !== '' ? $output : 'OK');
            } catch (\Throwable $e) {
                $lines[] = 'password_reset_tokens migration HATA: '.$e->getMessage();
            }

            try {
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path' => 'database/migrations/2024_06_21_000001_add_report_fields_to_user_notifications.php',
                ]);
                $output = trim(Artisan::output());
                $lines[] = 'report notification migration: '.($output !== '' ? $output : 'OK');
            } catch (\Throwable $e) {
                $lines[] = 'report notification migration HATA: '.$e->getMessage();
            }

            if (function_exists('opcache_reset')) {
                @opcache_reset();
                $lines[] = 'opcache reset';
            }
        }

        if (request('fix_env') === '1') {
            $envPath = base_path('.env');
            if (is_file($envPath) && is_writable($envPath)) {
                $content = (string) file_get_contents($envPath);
                $replacements = [
                    '/^APP_URL=.*/m' => 'APP_URL=https://www.gonulkoprusu.com',
                    '/^ADMIN_URL=.*/m' => 'ADMIN_URL=https://admin.gonulkoprusu.com',
                    '/^ASSET_URL=.*/m' => 'ASSET_URL=https://www.gonulkoprusu.com',
                    '/^ADMIN_SUBDOMAIN=.*/m' => 'ADMIN_SUBDOMAIN=false',
                ];
                foreach ($replacements as $pattern => $line) {
                    $content = preg_match($pattern, $content)
                        ? (string) preg_replace($pattern, $line, $content)
                        : $content."\n".$line;
                }
                file_put_contents($envPath, rtrim($content)."\n");
                $lines[] = '.env duzeltildi (APP_URL, ADMIN_URL, ADMIN_SUBDOMAIN)';
                try {
                    Artisan::call('config:clear');
                    $lines[] = 'config cache temizlendi (.env sonrasi)';
                } catch (\Throwable $e) {
                    $lines[] = 'config clear uyari: '.$e->getMessage();
                }
            } else {
                $lines[] = '.env duzeltilemedi (yok veya yazilamaz)';
            }
        }

        if (request('purge_all') === '1') {
            try {
                $stats = app(\App\Services\NotificationService::class)->purgeAll();
                $lines[] = 'tüm bildirimler silindi: '
                    .$stats['broadcasts'].' duyuru, '
                    .$stats['broadcast_reads'].' okuma kaydı, '
                    .$stats['user_notifications'].' kullanıcı bildirimi';
            } catch (\Throwable $e) {
                $lines[] = 'tüm bildirimler silme HATA: '.$e->getMessage();
            }
        }

        if (request('purge') === '1') {
            try {
                $count = app(\App\Services\NotificationService::class)->purgeExpired();
                $lines[] = 'bildirim temizliği: '.$count.' kayıt silindi';
            } catch (\Throwable $e) {
                $lines[] = 'bildirim temizliği HATA: '.$e->getMessage();
            }

            try {
                Artisan::call('stories:purge');
                $output = trim(Artisan::output());
                $lines[] = 'stories:purge: '.($output !== '' ? $output : 'OK');
            } catch (\Throwable $e) {
                $lines[] = 'stories:purge HATA: '.$e->getMessage();
            }
        }

        if (
            request()->boolean('pusher')
            || request()->boolean('realtime')
            || filter_var($_GET['pusher'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true
            || filter_var($_GET['realtime'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true
            || in_array((string) ($_GET['ws'] ?? ''), ['1', 'yes', 'on'], true)
        ) {
            $lines[] = '';
            $lines[] = '--- Pusher ---';
            $lines[] = 'BROADCAST_CONNECTION='.config('broadcasting.default');
            $lines[] = 'PUSHER key: '.(config('broadcasting.connections.pusher.key') ? 'tanimli' : 'yok');
            $lines[] = 'SDK: '.(class_exists(\Pusher\Pusher::class) ? 'yuklu' : 'eksik — composer require pusher/pusher-php-server');
            try {
                $realtime = app(\App\Services\RealtimeBroadcastService::class);
                $lines[] = 'Realtime enabled: '.($realtime->isEnabled() ? 'evet' : 'hayir');
            } catch (\Throwable $e) {
                $lines[] = 'Realtime kontrol: '.$e->getMessage();
            }

            if (request('install') === '1' && ! class_exists(\Pusher\Pusher::class)) {
                $base = base_path();
                $commands = [
                    'cd '.escapeshellarg($base).' && composer require pusher/pusher-php-server:^7.2 --no-interaction --no-progress 2>&1',
                    'cd '.escapeshellarg($base).' && php composer.phar require pusher/pusher-php-server:^7.2 --no-interaction --no-progress 2>&1',
                ];
                foreach ($commands as $command) {
                    $output = @shell_exec($command);
                    if (is_string($output) && trim($output) !== '') {
                        $lines[] = 'composer install: '.preg_replace('/\s+/', ' ', trim($output));
                        break;
                    }
                }
                $lines[] = 'SDK (sonra): '.(class_exists(\Pusher\Pusher::class) ? 'yuklu' : 'eksik');
            }
        }

        if (request('fcm') === '1') {
            $migration = 'database/migrations/2024_06_16_000001_create_device_tokens_table.php';

            try {
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path' => $migration,
                ]);
                $output = trim(Artisan::output());
                $lines[] = '';
                $lines[] = 'FCM migration: '.($output !== '' ? $output : 'OK');
            } catch (\Throwable $e) {
                $lines[] = 'FCM migration HATA: '.$e->getMessage();
            }

            try {
                $fcm = app(\App\Services\FcmPushService::class);
                $lines[] = 'FCM configured: '.($fcm->isConfigured() ? 'evet' : 'hayır');
                $lines[] = 'Kayıtlı cihaz: '.$fcm->registeredDeviceCount();
            } catch (\Throwable $e) {
                $lines[] = 'FCM kontrol: '.$e->getMessage();
            }
        }

        return response(
            implode("\n", $lines)."\n\nOK\n",
            200,
            [
                'Content-Type' => 'text/plain; charset=utf-8',
                'X-LiteSpeed-Purge' => '*',
            ]
        );
    }

    public function performance()
    {
        if (request('key') !== 'gk-perf-setup-2026') {
            abort(403);
        }

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/2024_06_13_000001_add_performance_indexes_and_sanctum.php',
            ]);
        } catch (\Throwable $e) {
            return response(
                'Migration hatasi: '.$e->getMessage()."\n",
                500,
                ['Content-Type' => 'text/plain; charset=utf-8']
            );
        }

        $output = trim(Artisan::output()) ?: 'Performance migration OK.';

        return response(
            $output."\n\nOK\n",
            200,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }

    public function deploySync()
    {
        if (request('key') !== 'gk-deploy-sync-2026') {
            abort(403);
        }

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(__FILE__, true);
        }

        $base = base_path();
        $lines = ['Gönül Köprüsü — deploy sync', 'base='.$base, 'opcache: sifirlandi', ''];

        foreach (glob($base.'/storage/framework/views/*.php') ?: [] as $file) {
            @unlink($file);
        }
        foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $name) {
            @unlink($base.'/bootstrap/cache/'.$name);
        }

        foreach (['view:clear', 'config:clear', 'route:clear'] as $command) {
            try {
                Artisan::call($command);
                $lines[] = $command.' ok';
            } catch (\Throwable $e) {
                $lines[] = $command.' uyarı: '.$e->getMessage();
            }
        }

        $serviceFile = $base.'/app/Services/PremiumPackagesService.php';
        $lines[] = 'PremiumPackagesService.php: '.(is_file($serviceFile) ? 'var' : 'YOK');
        $lines[] = 'User::packageBadge: '.(method_exists(\App\Models\User::class, 'packageBadge') ? 'var' : 'YOK');

        $logFile = $base.'/storage/logs/laravel.log';
        if (is_file($logFile) && is_readable($logFile)) {
            $lines[] = '';
            $lines[] = '--- laravel.log (tail) ---';
            $size = (int) @filesize($logFile);
            $fp = @fopen($logFile, 'rb');
            if ($fp) {
                @fseek($fp, max(0, $size - 12288));
                $chunk = trim((string) @stream_get_contents($fp));
                @fclose($fp);
                $logLines = preg_split("/\r\n|\n|\r/", $chunk) ?: [];
                foreach (array_slice($logLines, -30) as $line) {
                    $lines[] = $line;
                }
            } else {
                $lines[] = '(log okunamadı)';
            }
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'X-LiteSpeed-Purge' => '*',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function notifications()
    {
        if (request('key') !== 'gk-notifications-migrate-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — bildirim migration', 'base='.base_path(), ''];

        foreach ([
            'database/migrations/2024_06_10_000001_create_user_notifications_table.php',
            'database/migrations/2024_06_14_000001_add_message_id_to_user_notifications.php',
            'database/migrations/2024_06_21_000001_add_report_fields_to_user_notifications.php',
        ] as $path) {
            try {
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path' => $path,
                ]);
                $output = trim(Artisan::output());
                $lines[] = $path.': '.($output !== '' ? $output : 'OK');
            } catch (\Throwable $e) {
                $lines[] = $path.' HATA: '.$e->getMessage();
            }
        }

        try {
            $hasTable = \Illuminate\Support\Facades\Schema::hasTable('user_notifications');
            $hasMessageId = $hasTable && \Illuminate\Support\Facades\Schema::hasColumn('user_notifications', 'message_id');
            $lines[] = '';
            $lines[] = 'user_notifications tablosu: '.($hasTable ? 'var' : 'YOK');
            $lines[] = 'message_id sütunu: '.($hasMessageId ? 'var' : 'YOK');
        } catch (\Throwable $e) {
            $lines[] = 'schema kontrol hatası: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function cron()
    {
        if (request('key') !== 'gk-cron-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — zamanlanmış görevler', 'base='.base_path(), ''];

        foreach (['broadcasts:purge', 'stories:purge', 'messages:purge-old'] as $command) {
            try {
                Artisan::call($command);
                $output = trim(Artisan::output());
                $lines[] = $command.': '.($output !== '' ? $output : 'OK');
            } catch (\Throwable $e) {
                $lines[] = $command.' HATA: '.$e->getMessage();
            }
        }

        try {
            $lifecycle = app(\App\Services\GrowthLifecycleService::class)->run(30);
            $lines[] = 'growth-lifecycle: '.json_encode($lifecycle, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            $lines[] = 'growth-lifecycle HATA: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** Haftalık büyüme metrikleri — /setup/growth?key=gk-cpanel-setup-2026 */
    public function growth()
    {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — büyüme metrikleri (son 7 gün)', 'base='.base_path(), ''];

        try {
            $since = now()->subDays(7);
            $users = \App\Models\User::query()->where('role', 'user');

            $signups = (clone $users)->where('created_at', '>=', $since)->count();
            $female = (clone $users)->where('created_at', '>=', $since)->where('gender', 'female')->count();
            $male = (clone $users)->where('created_at', '>=', $since)->where('gender', 'male')->count();
            $withPhoto = (clone $users)->where('created_at', '>=', $since)->whereNotNull('profile_photo_url')->count();
            $referred = (clone $users)->where('created_at', '>=', $since)->whereNotNull('referred_by_user_id')->count();
            $google = (clone $users)->where('created_at', '>=', $since)->where('registration_source', 'google')->count();
            $seoCity = (clone $users)->where('created_at', '>=', $since)->where('utm_medium', 'city')->count();
            $instagram = (clone $users)->where('created_at', '>=', $since)->where('utm_source', 'instagram')->count();
            $meta = (clone $users)->where('created_at', '>=', $since)->where('utm_source', 'meta')->count();
            $googleAds = (clone $users)->where('created_at', '>=', $since)->whereIn('utm_source', ['google', 'googleads', 'adwords'])->count();
            $adsPaid = (clone $users)->where('created_at', '>=', $since)->where('utm_medium', 'paid')->count();
            $noPhoto = max(0, $signups - $withPhoto);
            $trialEnding = 0;
            try {
                $trialEnding = \App\Models\User::query()
                    ->where('role', 'user')
                    ->where('gender', 'male')
                    ->whereNotNull('trial_ends_at')
                    ->whereBetween('trial_ends_at', [now()->subDays(3), now()->addDay()])
                    ->count();
            } catch (\Throwable) {
            }

            $lines[] = 'kayıt_toplam='.$signups;
            $lines[] = 'kayıt_kadın='.$female;
            $lines[] = 'kayıt_erkek='.$male;
            $lines[] = 'kayıt_fotoğraflı='.$withPhoto.($signups > 0 ? ' ('.round($withPhoto / $signups * 100).'%)' : '');
            $lines[] = 'kayıt_fotosuz='.$noPhoto;
            $lines[] = 'davetle_gelen='.$referred.($signups > 0 ? ' ('.round($referred / $signups * 100).'%)' : '');
            $lines[] = 'google_kayıt='.$google;
            $lines[] = 'şehir_seo_utm='.$seoCity;
            $lines[] = 'instagram_utm='.$instagram;
            $lines[] = 'meta_utm='.$meta;
            $lines[] = 'google_ads_utm='.$googleAds;
            $lines[] = 'paid_utm='.$adsPaid;
            $lines[] = 'trial_bitis_penceresi_erkek='.$trialEnding;
            $lines[] = '';
            $lines[] = 'GTM event: sign_up, google_complete, google_login_click, invite_share, city_cta_click, instagram_cta, trial_cta_click';
            $lines[] = 'Instagram bio: https://gonulkoprusu.com/register?utm_source=instagram&utm_medium=bio&utm_campaign=organic';
            $lines[] = 'Ads landing: https://gonulkoprusu.com/kampanya?utm_source=meta&utm_medium=paid&utm_campaign=test1';
            $lines[] = 'Ads şehir: https://gonulkoprusu.com/kampanya?utm_source=meta&utm_medium=paid&utm_campaign=istanbul&city=istanbul';
            $lines[] = 'Cron lifecycle: https://gonulkoprusu.com/setup/cron?key=gk-cron-2026';
        } catch (\Throwable $e) {
            $lines[] = 'HATA: '.$e->getMessage();
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

        $lines = ['Gönül Köprüsü — FCM / device_tokens kurulum', 'base='.base_path(), ''];

        $migration = 'database/migrations/2024_06_16_000001_create_device_tokens_table.php';

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => $migration,
            ]);
            $output = trim(Artisan::output());
            $lines[] = $migration.': '.($output !== '' ? $output : 'OK');
        } catch (\Throwable $e) {
            $lines[] = $migration.' HATA: '.$e->getMessage();
        }

        try {
            $hasTable = \Illuminate\Support\Facades\Schema::hasTable('device_tokens');
            $lines[] = '';
            $lines[] = 'device_tokens tablosu: '.($hasTable ? 'var' : 'YOK');
        } catch (\Throwable $e) {
            $lines[] = 'schema kontrol hatası: '.$e->getMessage();
        }

        $credPath = config('firebase.credentials');
        $lines[] = 'credentials path: '.$credPath;
        $lines[] = 'credentials readable: '.(is_string($credPath) && is_readable($credPath) ? 'evet' : 'hayır');

        try {
            $fcm = app(\App\Services\FcmPushService::class);
            $lines[] = 'FCM configured: '.($fcm->isConfigured() ? 'evet' : 'hayır');
            $lines[] = 'Kayıtlı cihaz sayısı: '.$fcm->registeredDeviceCount();
        } catch (\Throwable $e) {
            $lines[] = 'FCM servis hatası: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'Service account JSON: Firebase Console → Project settings → Service accounts → Generate new private key';
        $lines[] = 'Sunucuya yükle: storage/app/firebase/gonulkoprusu-325eb.json';
        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function emailLogs()
    {
        if (request('key') !== 'gk-email-logs-migrate-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — e-posta log migration', 'base='.base_path(), ''];

        $path = 'database/migrations/2024_06_12_000001_create_email_logs_table.php';

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => $path,
            ]);
            $output = trim(Artisan::output());
            $lines[] = $path.': '.($output !== '' ? $output : 'OK');
        } catch (\Throwable $e) {
            $lines[] = $path.' HATA: '.$e->getMessage();
        }

        try {
            $hasTable = \Illuminate\Support\Facades\Schema::hasTable('email_logs');
            $lines[] = '';
            $lines[] = 'email_logs tablosu: '.($hasTable ? 'var' : 'YOK');
        } catch (\Throwable $e) {
            $lines[] = 'schema kontrol hatası: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function hobbies()
    {
        if (request('key') !== 'gk-hobbies-migrate-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — hobiler migration', 'base='.base_path(), ''];

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/2024_06_14_000002_add_hobbies_to_users_table.php',
            ]);
            $output = trim(Artisan::output());
            $lines[] = 'migration: '.($output !== '' ? $output : 'OK');
        } catch (\Throwable $e) {
            $lines[] = 'migration HATA: '.$e->getMessage();
        }

        try {
            $has = \Illuminate\Support\Facades\Schema::hasColumn('users', 'hobbies');
            $lines[] = 'users.hobbies sütunu: '.($has ? 'var' : 'YOK');
        } catch (\Throwable $e) {
            $lines[] = 'schema kontrol hatası: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function locale()
    {
        if (request('key') !== 'gk-locale-migrate-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — dil migration', 'base='.base_path(), ''];

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/2024_06_14_000003_add_locale_to_users_table.php',
            ]);
            $output = trim(Artisan::output());
            $lines[] = 'migration: '.($output !== '' ? $output : 'OK');
        } catch (\Throwable $e) {
            $lines[] = 'migration HATA: '.$e->getMessage();
        }

        try {
            $has = \Illuminate\Support\Facades\Schema::hasColumn('users', 'locale');
            $lines[] = 'users.locale sütunu: '.($has ? 'var' : 'YOK');
        } catch (\Throwable $e) {
            $lines[] = 'schema kontrol hatası: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
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

        try {
            $hasSender = \Illuminate\Support\Facades\Schema::hasColumn('messages', 'hidden_for_sender_at');
            $hasReceiver = \Illuminate\Support\Facades\Schema::hasColumn('messages', 'hidden_for_receiver_at');
            $lines[] = 'hidden_for_sender_at: '.($hasSender ? 'var' : 'YOK');
            $lines[] = 'hidden_for_receiver_at: '.($hasReceiver ? 'var' : 'YOK');
        } catch (\Throwable $e) {
            $lines[] = 'schema kontrol hatası: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function profileFields()
    {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $lines = ['Gönül Köprüsü — profil alanları şema', 'base='.base_path(), ''];

        try {
            foreach ([
                'bio' => fn ($table) => $table->text('bio')->nullable(),
                'birth_date' => fn ($table) => $table->date('birth_date')->nullable(),
                'relationship_status' => fn ($table) => $table->string('relationship_status', 32)->nullable(),
            ] as $column => $definition) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', $column)) {
                    $lines[] = "users.{$column}: var";
                    continue;
                }

                \Illuminate\Support\Facades\Schema::table('users', function ($table) use ($definition) {
                    $definition($table);
                });
                $lines[] = "users.{$column}: eklendi";
            }
        } catch (\Throwable $e) {
            $lines[] = 'schema HATA: '.$e->getMessage();
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function deleteUsers()
    {
        if (request('key') !== 'gk-delete-users-2026') {
            abort(403);
        }

        $names = array_values(array_filter(array_map(
            static fn ($name) => trim($name),
            explode(',', (string) request('users', ''))
        )));

        if ($names === []) {
            return response("users parametresi gerekli (ör. users=rida453,murat)\n", 400, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        }

        $lines = ['Gönül Köprüsü — kullanıcı silme', 'base='.base_path(), ''];

        foreach ($names as $name) {
            $user = \App\Models\User::query()
                ->where('role', 'user')
                ->whereRaw('LOWER(username) = ?', [mb_strtolower($name)])
                ->first();

            if (! $user) {
                $similar = \App\Models\User::query()
                    ->where('role', 'user')
                    ->where('username', 'like', '%'.str_replace(['%', '_'], '', $name).'%')
                    ->orderBy('username')
                    ->limit(10)
                    ->pluck('username');

                $lines[] = $name.': bulunamadı'.($similar->isNotEmpty() ? ' (benzer: '.$similar->implode(', ').')' : '');
                continue;
            }

            if ($user->isAdmin()) {
                $lines[] = "{$user->username}: yönetici — silinmedi";
                continue;
            }

            try {
                $id = $user->id;
                $username = $user->username;
                $user->delete();
                $lines[] = "{$username} (id={$id}): silindi";
            } catch (\Throwable $e) {
                $lines[] = "{$user->username}: HATA — ".$e->getMessage();
            }
        }

        $lines[] = '';
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
