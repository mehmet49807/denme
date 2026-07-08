<?php

use App\Http\Controllers\Web\GoogleAuthController;
use App\Http\Controllers\Web\AuthPageController;
use App\Http\Controllers\Web\FeedPageController;
use App\Http\Controllers\Web\PostPageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\LegalPageController;
use App\Http\Controllers\Web\LocationUsersPageController;
use App\Http\Controllers\Web\CitySeoPageController;
use App\Http\Controllers\Web\SupportPageController;
use App\Http\Controllers\Web\ReferralPageController;
use App\Http\Controllers\Web\MessagePageController;
use App\Http\Controllers\Web\LiveSyncController;
use App\Http\Controllers\Web\NotificationPageController;
use App\Http\Controllers\Web\PremiumPageController;
use App\Http\Controllers\Web\ProfilePageController;
use App\Http\Controllers\Web\StoryPageController;
use App\Http\Controllers\Web\UserProfilePageController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

$gkHttpHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
if ($gkHttpHost === 'gonulkoprusu.com') {
    header('Location: https://www.gonulkoprusu.com'.($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

$requestHost = strtolower(preg_replace('/:\d+$/', '', (string) (request()->getHost() ?: ($_SERVER['HTTP_HOST'] ?? ''))));
$publicHosts = ['gonulkoprusu.com', 'www.gonulkoprusu.com'];
$adminHosts = ['admin.gonulkoprusu.com'];

// Ana site (apex + www) her zaman kullanici arayuzu; panel yalnizca admin alt alaninda.
if (filter_var(env('ADMIN_SUBDOMAIN', false), FILTER_VALIDATE_BOOL) || (! in_array($requestHost, $publicHosts, true) && in_array($requestHost, $adminHosts, true))) {
    require __DIR__.'/admin_subdomain.php';

    return;
}

// Public landing
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/gizlilik-politikasi', [LegalPageController::class, 'privacy'])->name('privacy');
Route::redirect('/gizlilik-sozlesmesi', '/gizlilik-politikasi', 301);

if (class_exists(\App\Http\Controllers\Web\SetupController::class)) {
    Route::get('/setup/cpanel', [\App\Http\Controllers\Web\SetupController::class, 'cpanel']);
    Route::get('/setup/performance', [\App\Http\Controllers\Web\SetupController::class, 'performance']);
    Route::get('/setup/deploy-sync', [\App\Http\Controllers\Web\SetupController::class, 'deploySync']);
    Route::get('/setup/notifications', [\App\Http\Controllers\Web\SetupController::class, 'notifications']);
    Route::get('/setup/fcm', [\App\Http\Controllers\Web\SetupController::class, 'fcm']);
    Route::get('/setup/email-logs', [\App\Http\Controllers\Web\SetupController::class, 'emailLogs']);
    Route::get('/setup/hobbies', [\App\Http\Controllers\Web\SetupController::class, 'hobbies']);
    Route::get('/setup/locale', [\App\Http\Controllers\Web\SetupController::class, 'locale']);
    Route::get('/setup/delete-users', [\App\Http\Controllers\Web\SetupController::class, 'deleteUsers']);
    Route::get('/setup/messages', [\App\Http\Controllers\Web\SetupController::class, 'messagesSchema']);
    Route::get('/setup/cron', [\App\Http\Controllers\Web\SetupController::class, 'cron']);
    Route::get('/setup/ws-check', function () {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $lines = [
            'Gönül Köprüsü — Pusher kontrol',
            'BROADCAST_CONNECTION='.config('broadcasting.default'),
            'PUSHER key: '.(config('broadcasting.connections.pusher.key') ? 'tanimli' : 'yok'),
            'PUSHER cluster: '.config('broadcasting.connections.pusher.options.cluster', 'eu'),
            'SDK: '.(class_exists(\Pusher\Pusher::class) ? 'yuklu' : 'eksik'),
        ];

        try {
            $realtime = app(\App\Services\RealtimeBroadcastService::class);
            $lines[] = 'Realtime enabled: '.($realtime->isEnabled() ? 'evet' : 'hayir');
        } catch (\Throwable $e) {
            $lines[] = 'Realtime kontrol: '.$e->getMessage();
        }

        return response(implode("\n", $lines)."\n\nOK\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    });
    Route::get('/setup/clear-cache', function () {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        foreach (['route:clear', 'view:clear', 'config:clear', 'cache:clear'] as $command) {
            try {
                Artisan::call($command);
            } catch (\Throwable $e) {
                // Hosting kısıtlarında bazı komutlar başarısız olabilir.
            }
        }

        return response("Cache temizlendi.\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    });
    Route::get('/setup/diag-blog-sss', function () {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $checks = [
            'routes' => [
                'blog' => \Illuminate\Support\Facades\Route::has('blog'),
                'sss' => \Illuminate\Support\Facades\Route::has('sss'),
                'blog.show' => \Illuminate\Support\Facades\Route::has('blog.show'),
            ],
            'views' => [
                'blog' => view()->exists('web.blog'),
                'sss' => view()->exists('web.sss'),
                'legal-nav' => view()->exists('partials.legal-nav'),
            ],
            'render' => [],
        ];

        $legalData = [
            'lastUpdated' => '5 Haziran 2026',
            'contactEmail' => 'destek@gonulkoprusu.com',
            'faqItems' => [],
            'posts' => [],
        ];

        foreach (['partials.legal-nav' => ['active' => 'sss'], 'web.sss' => $legalData, 'web.blog' => array_merge($legalData, ['posts' => []])] as $view => $data) {
            try {
                view($view, $data)->render();
                $checks['render'][$view] = 'ok';
            } catch (\Throwable $e) {
                $checks['render'][$view] = $e->getMessage();
            }
        }

        return response()->json($checks, 200, ['Cache-Control' => 'no-store']);
    });
    Route::get('/setup/restore-critical-views', function () {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }
        $payload = json_decode(<<<'JSON'
{"resources/views/layouts/app.blade.php": "PCFET0NUWVBFIGh0bWw+CjxodG1sIGxhbmc9Int7IHN0cl9yZXBsYWNlKCdfJywgJy0nLCBhcHAoKS0+Z2V0TG9jYWxlKCkpIH19Ij4KQHBocAogICAgJGlzTGFuZGluZyA9IHJlcXVlc3QoKS0+cm91dGVJcygnaG9tZScpOwogICAgJGFwcFNoZWxsID0gdHJpbSgkX19lbnYtPnlpZWxkQ29udGVudCgnYm9keS1jbGFzcycpKSA9PT0gJ2FwcC1zaGVsbCc7CiAgICAkaXNDb250ZW50UGFnZSA9IHN0cl9jb250YWlucyh0cmltKCRfX2Vudi0+eWllbGRDb250ZW50KCdib2R5LWNsYXNzJykpLCAncGFnZS1jb250ZW50Jyk7CiAgICAkaXNBdXRoUGFnZSA9IHN0cl9jb250YWlucyh0cmltKCRfX2Vudi0+eWllbGRDb250ZW50KCdib2R5LWNsYXNzJykpLCAncGFnZS1hdXRoJyk7CkBlbmRwaHAKPGhlYWQ+CiAgICA8bWV0YSBjaGFyc2V0PSJVVEYtOCI+CiAgICA8bWV0YSBuYW1lPSJ2aWV3cG9ydCIgY29udGVudD0id2lkdGg9ZGV2aWNlLXdpZHRoLCBpbml0aWFsLXNjYWxlPTEuMCwgdmlld3BvcnQtZml0PWNvdmVyIj4KICAgIDxtZXRhIG5hbWU9ImNzcmYtdG9rZW4iIGNvbnRlbnQ9Int7IGNzcmZfdG9rZW4oKSB9fSI+CiAgICA8dGl0bGU+QHlpZWxkKCd0aXRsZScsIF9fKCdhcHAuYnJhbmQnKSk8L3RpdGxlPgogICAgQGluY2x1ZGUoJ3BhcnRpYWxzLnNlby1oZWFkJykKICAgIEBzdGFjaygnaGVhZCcpCiAgICA8bGluayByZWw9Imljb24iIGhyZWY9Int7IGFzc2V0KCdpbWFnZXMvZmF2aWNvbi5wbmcnKSB9fT92PXt7IGNvbmZpZygnYnJhbmQubG9nb192ZXJzaW9uJykgfX0iIHNpemVzPSIzMngzMiIgdHlwZT0iaW1hZ2UvcG5nIj4KICAgIDxsaW5rIHJlbD0iaWNvbiIgaHJlZj0ie3sgYXNzZXQoJ2ltYWdlcy9mYXZpY29uLnN2ZycpIH19P3Y9e3sgY29uZmlnKCdicmFuZC5sb2dvX3ZlcnNpb24nKSB9fSIgdHlwZT0iaW1hZ2Uvc3ZnK3htbCI+CiAgICA8bGluayByZWw9ImFwcGxlLXRvdWNoLWljb24iIGhyZWY9Int7IGFzc2V0KCdpbWFnZXMvYXBwbGUtdG91Y2gtaWNvbi5wbmcnKSB9fT92PXt7IGNvbmZpZygnYnJhbmQubG9nb192ZXJzaW9uJykgfX0iPgogICAgQGluY2x1ZGUoJ3BhcnRpYWxzLmFzeW5jLWZvbnRzJykKICAgIEBpZigkaXNMYW5kaW5nKQogICAgQHBocAogICAgICAgICRoZXJvNjQwID0gaXNfZmlsZShiYXNlX3BhdGgoJ2ltYWdlcy9sYW5kaW5nLWhlcm8tY291cGxlLTY0MC53ZWJwJykpOwogICAgICAgICRoZXJvOTYwID0gaXNfZmlsZShiYXNlX3BhdGgoJ2ltYWdlcy9sYW5kaW5nLWhlcm8tY291cGxlLTk2MC53ZWJwJykpOwogICAgQGVuZHBocAogICAgQGlmKCRoZXJvNjQwKQogICAgPGxpbmsgcmVsPSJwcmVsb2FkIiBhcz0iaW1hZ2UiIGhyZWY9Int7IGFzc2V0KCdpbWFnZXMvbGFuZGluZy1oZXJvLWNvdXBsZS02NDAud2VicD92PW9wdC12NicpIH19IiB0eXBlPSJpbWFnZS93ZWJwIiBmZXRjaHByaW9yaXR5PSJoaWdoIiBtZWRpYT0iKG1heC13aWR0aDogNzY4cHgpIj4KICAgIEBlbmRpZgogICAgQGlmKCRoZXJvOTYwKQogICAgPGxpbmsgcmVsPSJwcmVsb2FkIiBhcz0iaW1hZ2UiIGhyZWY9Int7IGFzc2V0KCdpbWFnZXMvbGFuZGluZy1oZXJvLWNvdXBsZS05NjAud2VicD92PW9wdC12NicpIH19IiB0eXBlPSJpbWFnZS93ZWJwIiBmZXRjaHByaW9yaXR5PSJoaWdoIiBtZWRpYT0iKG1pbi13aWR0aDogNzY5cHgpIj4KICAgIEBlbHNlaWYoJGhlcm82NDApCiAgICA8bGluayByZWw9InByZWxvYWQiIGFzPSJpbWFnZSIgaHJlZj0ie3sgYXNzZXQoJ2ltYWdlcy9sYW5kaW5nLWhlcm8tY291cGxlLTY0MC53ZWJwP3Y9b3B0LXY2JykgfX0iIHR5cGU9ImltYWdlL3dlYnAiIGZldGNocHJpb3JpdHk9ImhpZ2giIG1lZGlhPSIobWluLXdpZHRoOiA3NjlweCkiPgogICAgQGVsc2UKICAgIDxsaW5rIHJlbD0icHJlbG9hZCIgYXM9ImltYWdlIiBocmVmPSJ7eyBhc3NldCgnaW1hZ2VzL2xhbmRpbmctaGVyby1jb3VwbGUud2VicD92PW9wdC12NicpIH19IiB0eXBlPSJpbWFnZS93ZWJwIiBmZXRjaHByaW9yaXR5PSJoaWdoIj4KICAgIEBlbmRpZgogICAgQGluY2x1ZGUoJ3BhcnRpYWxzLmxhbmRpbmctaW5saW5lLWNzcycpCiAgICBAZWxzZQogICAgPGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSJ7eyBhc3NldCgnY3NzL2FwcC5jc3MnKSB9fT92PWFwcC12NDEiPgogICAgQGVuZGlmCiAgICBAYXV0aAogICAgQHBocCAkcmVhbHRpbWVFbmFibGVkID0gYXBwKFxBcHBcU2VydmljZXNcUmVhbHRpbWVCcm9hZGNhc3RTZXJ2aWNlOjpjbGFzcyktPmlzRW5hYmxlZCgpOyBAZW5kcGhwCiAgICA8bWV0YSBuYW1lPSJiYWRnZXMtdXJsIiBjb250ZW50PSJ7eyByb3V0ZSgnbm90aWZpY2F0aW9ucy5iYWRnZS1jb3VudHMnKSB9fSI+CiAgICA8bWV0YSBuYW1lPSJsaXZlLXN5bmMtdXJsIiBjb250ZW50PSJ7eyByb3V0ZSgnbGl2ZS5zeW5jJykgfX0iPgogICAgQGlmKCRyZWFsdGltZUVuYWJsZWQpCiAgICA8bWV0YSBuYW1lPSJhdXRoLXVzZXItaWQiIGNvbnRlbnQ9Int7IGF1dGgoKS0+aWQoKSB9fSI+CiAgICA8bWV0YSBuYW1lPSJwdXNoZXIta2V5IiBjb250ZW50PSJ7eyBjb25maWcoJ2Jyb2FkY2FzdGluZy5jb25uZWN0aW9ucy5wdXNoZXIua2V5JykgfX0iPgogICAgPG1ldGEgbmFtZT0icHVzaGVyLWNsdXN0ZXIiIGNvbnRlbnQ9Int7IGNvbmZpZygnYnJvYWRjYXN0aW5nLmNvbm5lY3Rpb25zLnB1c2hlci5vcHRpb25zLmNsdXN0ZXInLCAnZXUnKSB9fSI+CiAgICA8bWV0YSBuYW1lPSJwdXNoZXItYXV0aC11cmwiIGNvbnRlbnQ9Int7IHVybCgnL2Jyb2FkY2FzdGluZy9hdXRoJykgfX0iPgogICAgQGVuZGlmCiAgICBAaW5jbHVkZSgncGFydGlhbHMubGl2ZS1zeW5jLW1ldGEnKQogICAgQHN0YWNrKCdoZWFkLW1ldGEnKQogICAgQGVuZGF1dGgKPC9oZWFkPgo8Ym9keSBjbGFzcz0ie3sgdHJpbSgoJGFwcFNoZWxsID8gJ2FwcC1zaGVsbC1ib2R5JyA6ICcnKSAuICcgJyAuICgkaXNMYW5kaW5nID8gJ3BhZ2UtbGFuZGluZycgOiAnJykgLiAnICcgLiAoJGlzQ29udGVudFBhZ2UgPyAncGFnZS1jb250ZW50JyA6ICcnKSAuICcgJyAuICgkaXNBdXRoUGFnZSA/ICdwYWdlLWF1dGgnIDogJycpKSB9fSI+CkBpbmNsdWRlKCdwYXJ0aWFscy5nb29nbGUtdGFnLW1hbmFnZXItYm9keScpCiAgICA8aGVhZGVyIGNsYXNzPSJzaXRlLWhlYWRlciB7eyAkaXNMYW5kaW5nIHx8ICRpc0F1dGhQYWdlID8gJ3NpdGUtaGVhZGVyLS1sYW5kaW5nJyA6ICcnIH19Ij4KICAgICAgICA8ZGl2IGNsYXNzPSJzaXRlLWhlYWRlci1pbm5lciI+CiAgICAgICAgICAgIEBpbmNsdWRlKCdwYXJ0aWFscy5sb2dvJywgWydzaG93VGFnbGluZScgPT4gdHJ1ZV0pCgogICAgICAgICAgICBAdW5sZXNzKCRhcHBTaGVsbCkKICAgICAgICAgICAgPG5hdiBjbGFzcz0ic2l0ZS1uYXYiIGFyaWEtbGFiZWw9Int7IF9fKCdhcHAubmF2Lm1haW4nKSB9fSI+CiAgICAgICAgICAgICAgICA8YSBocmVmPSJ7eyByb3V0ZSgnaG9tZScpIH19Ij57eyBfXygnYXBwLm5hdi5ob21lJykgfX08L2E+CiAgICAgICAgICAgICAgICA8YSBocmVmPSJ7eyByb3V0ZSgnYWJvdXQnKSB9fSI+e3sgX18oJ2FwcC5uYXYuYWJvdXQnKSB9fTwvYT4KICAgICAgICAgICAgICAgIDxhIGhyZWY9Int7IHJvdXRlKCdzYWZlLW1lZXRpbmcnKSB9fSI+e3sgX18oJ2FwcC5uYXYuc2VjdXJpdHknKSB9fTwvYT4KICAgICAgICAgICAgICAgIDxhIGhyZWY9Int7IHVybCgnL2Jsb2cnKSB9fSI+e3sgX18oJ2FwcC5uYXYuYmxvZycpIH19PC9hPgogICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgdXJsKCcvc3NzJykgfX0iPnt7IF9fKCdhcHAubmF2LnNzcycpIH19PC9hPgogICAgICAgICAgICAgICAgQGF1dGgKICAgICAgICAgICAgICAgICAgICBAcGhwCiAgICAgICAgICAgICAgICAgICAgICAgICR1bnJlYWROb3RpZmljYXRpb25zID0gJHVucmVhZE5vdGlmaWNhdGlvbnMgPz8gMDsKICAgICAgICAgICAgICAgICAgICAgICAgJHVucmVhZE1lc3NhZ2VzID0gJHVucmVhZE1lc3NhZ2VzID8/IDA7CiAgICAgICAgICAgICAgICAgICAgQGVuZHBocAogICAgICAgICAgICAgICAgICAgIDxhIGhyZWY9Int7IHJvdXRlKCdmZWVkJykgfX0iPnt7IF9fKCdhcHAubmF2LmZlZWQnKSB9fTwvYT4KICAgICAgICAgICAgICAgICAgICA8YSBocmVmPSJ7eyByb3V0ZSgnbm90aWZpY2F0aW9ucy5pbmRleCcpIH19IiBkYXRhLW5hdi1iYWRnZT0ibm90aWZpY2F0aW9ucyI+CiAgICAgICAgICAgICAgICAgICAgICAgIHt7IF9fKCdhcHAubmF2Lm5vdGlmaWNhdGlvbnMnKSB9fQogICAgICAgICAgICAgICAgICAgICAgICBAaWYoJHVucmVhZE5vdGlmaWNhdGlvbnMgPiAwKQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9InNpdGUtbmF2LWJhZGdlIj57eyAkdW5yZWFkTm90aWZpY2F0aW9ucyB9fTwvc3Bhbj4KICAgICAgICAgICAgICAgICAgICAgICAgQGVuZGlmCiAgICAgICAgICAgICAgICAgICAgPC9hPgogICAgICAgICAgICAgICAgICAgIDxhIGhyZWY9Int7IHJvdXRlKCdtZXNzYWdlcy5pbmRleCcpIH19IiBkYXRhLW5hdi1iYWRnZT0ibWVzc2FnZXMiPgogICAgICAgICAgICAgICAgICAgICAgICB7eyBfXygnYXBwLm5hdi5tZXNzYWdlcycpIH19CiAgICAgICAgICAgICAgICAgICAgICAgIEBpZigkdW5yZWFkTWVzc2FnZXMgPiAwKQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9InNpdGUtbmF2LWJhZGdlIj57eyAkdW5yZWFkTWVzc2FnZXMgfX08L3NwYW4+CiAgICAgICAgICAgICAgICAgICAgICAgIEBlbmRpZgogICAgICAgICAgICAgICAgICAgIDwvYT4KICAgICAgICAgICAgICAgICAgICA8YSBocmVmPSJ7eyByb3V0ZSgncHJvZmlsZScpIH19Ij57eyBfXygnYXBwLm5hdi5wcm9maWxlJykgfX08L2E+CiAgICAgICAgICAgICAgICAgICAgQGlmKGF1dGgoKS0+dXNlcigpLT5nZW5kZXIgPT09ICdtYWxlJykKICAgICAgICAgICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgcm91dGUoJ3ByZW1pdW0nKSB9fSI+e3sgX18oJ2FwcC5uYXYucHJlbWl1bScpIH19PC9hPgogICAgICAgICAgICAgICAgICAgIEBlbmRpZgogICAgICAgICAgICAgICAgICAgIEBpZihhdXRoKCktPnVzZXIoKS0+aXNBZG1pbigpICYmIFxJbGx1bWluYXRlXFN1cHBvcnRcRmFjYWRlc1xSb3V0ZTo6aGFzKCdhZG1pbi5kYXNoYm9hcmQnKSkKICAgICAgICAgICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgcm91dGUoJ2FkbWluLmRhc2hib2FyZCcpIH19Ij57eyBfXygnYXBwLm5hdi5hZG1pbicpIH19PC9hPgogICAgICAgICAgICAgICAgICAgIEBlbmRpZgogICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPSJzaXRlLW5hdi1sb2dvdXQiPgogICAgICAgICAgICAgICAgICAgICAgICA8Zm9ybSBhY3Rpb249Int7IHJvdXRlKCdsb2dvdXQnKSB9fSIgbWV0aG9kPSJQT1NUIj5AY3NyZjxidXR0b24gdHlwZT0ic3VibWl0Ij57eyBfXygnYXBwLm5hdi5sb2dvdXQnKSB9fTwvYnV0dG9uPjwvZm9ybT4KICAgICAgICAgICAgICAgICAgICA8L3NwYW4+CiAgICAgICAgICAgICAgICBAZW5kYXV0aAogICAgICAgICAgICAgICAgQGd1ZXN0CiAgICAgICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgcm91dGUoJ2xvZ2luJykgfX0iIGNsYXNzPSJzaXRlLW5hdi1sb2dpbiI+e3sgX18oJ2FwcC5uYXYubG9naW4nKSB9fTwvYT4KICAgICAgICAgICAgICAgICAgICA8YSBocmVmPSJ7eyByb3V0ZSgncmVnaXN0ZXInKSB9fSIgY2xhc3M9ImJ0biBidG4tcHJpbWFyeSBidG4tc20iPnt7IF9fKCdhcHAubmF2LnJlZ2lzdGVyJykgfX08L2E+CiAgICAgICAgICAgICAgICBAZW5kZ3Vlc3QKICAgICAgICAgICAgPC9uYXY+CiAgICAgICAgICAgIEBlbmR1bmxlc3MKICAgICAgICA8L2Rpdj4KICAgIDwvaGVhZGVyPgoKICAgIDxtYWluIGNsYXNzPSJzaXRlLW1haW4gQHlpZWxkKCdtYWluLWNsYXNzJykge3sgJGlzTGFuZGluZyA/ICdzaXRlLW1haW4tLWxhbmRpbmcnIDogJycgfX0iPgogICAgICAgIEBpZihzZXNzaW9uKCdzdWNjZXNzJykpCiAgICAgICAgICAgIDxkaXYgY2xhc3M9ImZsYXNoLXN1Y2Nlc3MiPnt7IHNlc3Npb24oJ3N1Y2Nlc3MnKSB9fTwvZGl2PgogICAgICAgIEBlbmRpZgogICAgICAgIEB5aWVsZCgnY29udGVudCcpCiAgICA8L21haW4+CgogICAgQHVubGVzcygkYXBwU2hlbGwgfHwgJGlzQXV0aFBhZ2UpCiAgICAgICAgQGluY2x1ZGUoJ3BhcnRpYWxzLmZvb3RlcicpCiAgICBAZW5kdW5sZXNzCiAgICBAYXV0aAogICAgQHBocCAkcmVhbHRpbWVFbmFibGVkID0gYXBwKFxBcHBcU2VydmljZXNcUmVhbHRpbWVCcm9hZGNhc3RTZXJ2aWNlOjpjbGFzcyktPmlzRW5hYmxlZCgpOyBAZW5kcGhwCiAgICA8c2NyaXB0IHNyYz0ie3sgYXNzZXQoJ2pzL2JhZGdlcy5qcycpIH19P3Y9YmFkZ2VzLXY1Ij48L3NjcmlwdD4KICAgIEBpZigkcmVhbHRpbWVFbmFibGVkKQogICAgPHNjcmlwdCBzcmM9Imh0dHBzOi8vanMucHVzaGVyLmNvbS84LjQuMC9wdXNoZXIubWluLmpzIiBjcm9zc29yaWdpbj0iYW5vbnltb3VzIj48L3NjcmlwdD4KICAgIDxzY3JpcHQgc3JjPSJodHRwczovL2Nkbi5qc2RlbGl2ci5uZXQvbnBtL2xhcmF2ZWwtZWNob0AxLjE2LjEvZGlzdC9lY2hvLmlpZmUuanMiIGNyb3Nzb3JpZ2luPSJhbm9ueW1vdXMiPjwvc2NyaXB0PgogICAgPHNjcmlwdCBzcmM9Int7IGFzc2V0KCdqcy9ydC1jbGllbnQuanMnKSB9fT92PXJ0LWNsaWVudC12MSI+PC9zY3JpcHQ+CiAgICBAZW5kaWYKICAgIDxzY3JpcHQgc3JjPSJ7eyBhc3NldCgnanMvbGl2ZS1zeW5jLmpzJykgfX0/dj1saXZlLXN5bmMtdjMiPjwvc2NyaXB0PgogICAgPHNjcmlwdCBzcmM9Int7IGFzc2V0KCdqcy9wYWdlLWF1dG8tcmVmcmVzaC5qcycpIH19P3Y9cGFnZS1hdXRvLXJlZnJlc2gtdjEiPjwvc2NyaXB0PgogICAgQHN0YWNrKCdwYWdlLXNjcmlwdHMnKQogICAgQGVuZGF1dGgKICAgIEBzdGFjaygnbGQtanNvbicpCiAgICBAaW5jbHVkZSgncGFydGlhbHMuZGVmZXJyZWQtYW5hbHl0aWNzJykKPC9ib2R5Pgo8L2h0bWw+Cg==", "resources/views/web/home.blade.php": "QGV4dGVuZHMoJ2xheW91dHMuYXBwJykKCkBzZWN0aW9uKCd0aXRsZScsICdHw7Zuw7xsIEvDtnByw7xzw7wg4oCUIEV2bGlsaWsgdmUgVGFuxLHFn21hIFBsYXRmb3JtdScpCgpAc2VjdGlvbignY29udGVudCcpCjxzZWN0aW9uIGNsYXNzPSJsYW5kaW5nLWhlcm8iPgogICAgPGRpdiBjbGFzcz0ibGFuZGluZy1oZXJvLWdsb3cgbGFuZGluZy1oZXJvLWdsb3ctLWEiIGFyaWEtaGlkZGVuPSJ0cnVlIj48L2Rpdj4KICAgIDxkaXYgY2xhc3M9ImxhbmRpbmctaGVyby1nbG93IGxhbmRpbmctaGVyby1nbG93LS1iIiBhcmlhLWhpZGRlbj0idHJ1ZSI+PC9kaXY+CiAgICA8ZGl2IGNsYXNzPSJsYW5kaW5nLWhlcm8tZ2xvdyBsYW5kaW5nLWhlcm8tZ2xvdy0tYyIgYXJpYS1oaWRkZW49InRydWUiPjwvZGl2PgogICAgPGRpdiBjbGFzcz0ibGFuZGluZy1oZXJvLWJnIiBhcmlhLWhpZGRlbj0idHJ1ZSI+CiAgICAgICAgQHBocAogICAgICAgICAgICAkaGVyb1ZlcnNpb24gPSAnb3B0LXY2JzsKICAgICAgICAgICAgJGhlcm9XaWR0aHMgPSBbNjQwLCA5NjAsIDEyODBdOwogICAgICAgICAgICAkaGVyb1dlYnAgPSBbXTsKICAgICAgICAgICAgJGhlcm9KcGcgPSBbXTsKICAgICAgICAgICAgZm9yZWFjaCAoJGhlcm9XaWR0aHMgYXMgJHcpIHsKICAgICAgICAgICAgICAgICRzdWZmaXggPSAkdyA9PT0gMTI4MCA/ICcnIDogIi17JHd9IjsKICAgICAgICAgICAgICAgICRiYXNlID0gJ2xhbmRpbmctaGVyby1jb3VwbGUnLiRzdWZmaXg7CiAgICAgICAgICAgICAgICBpZiAoaXNfZmlsZShiYXNlX3BhdGgoImltYWdlcy97JGJhc2V9LndlYnAiKSkpIHsKICAgICAgICAgICAgICAgICAgICAkaGVyb1dlYnBbXSA9IGFzc2V0KCJpbWFnZXMveyRiYXNlfS53ZWJwP3Y9eyRoZXJvVmVyc2lvbn0iKS4iIHskd313IjsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGlmIChpc19maWxlKGJhc2VfcGF0aCgiaW1hZ2VzL3skYmFzZX0uanBnIikpKSB7CiAgICAgICAgICAgICAgICAgICAgJGhlcm9KcGdbXSA9IGFzc2V0KCJpbWFnZXMveyRiYXNlfS5qcGc/dj17JGhlcm9WZXJzaW9ufSIpLiIgeyR3fXciOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgICRoZXJvRmFsbGJhY2tXZWJwID0gaXNfZmlsZShiYXNlX3BhdGgoJ2ltYWdlcy9sYW5kaW5nLWhlcm8tY291cGxlLTY0MC53ZWJwJykpCiAgICAgICAgICAgICAgICA/IGFzc2V0KCJpbWFnZXMvbGFuZGluZy1oZXJvLWNvdXBsZS02NDAud2VicD92PXskaGVyb1ZlcnNpb259IikKICAgICAgICAgICAgICAgIDogYXNzZXQoImltYWdlcy9sYW5kaW5nLWhlcm8tY291cGxlLndlYnA/dj17JGhlcm9WZXJzaW9ufSIpOwogICAgICAgICAgICAkaGVyb0ZhbGxiYWNrSnBnID0gaXNfZmlsZShiYXNlX3BhdGgoJ2ltYWdlcy9sYW5kaW5nLWhlcm8tY291cGxlLTY0MC5qcGcnKSkKICAgICAgICAgICAgICAgID8gYXNzZXQoImltYWdlcy9sYW5kaW5nLWhlcm8tY291cGxlLTY0MC5qcGc/dj17JGhlcm9WZXJzaW9ufSIpCiAgICAgICAgICAgICAgICA6IGFzc2V0KCJpbWFnZXMvbGFuZGluZy1oZXJvLWNvdXBsZS5qcGc/dj17JGhlcm9WZXJzaW9ufSIpOwogICAgICAgIEBlbmRwaHAKICAgICAgICA8cGljdHVyZT4KICAgICAgICAgICAgQGlmKCRoZXJvV2VicCAhPT0gW10pCiAgICAgICAgICAgICAgICA8c291cmNlIHNyY3NldD0ie3sgaW1wbG9kZSgnLCAnLCAkaGVyb1dlYnApIH19IiBzaXplcz0iKG1heC13aWR0aDogNzY4cHgpIDY0MHB4LCAxMDB2dyIgdHlwZT0iaW1hZ2Uvd2VicCI+CiAgICAgICAgICAgIEBlbHNlCiAgICAgICAgICAgICAgICA8c291cmNlIHNyY3NldD0ie3sgJGhlcm9GYWxsYmFja1dlYnAgfX0iIHR5cGU9ImltYWdlL3dlYnAiPgogICAgICAgICAgICBAZW5kaWYKICAgICAgICAgICAgPGltZwogICAgICAgICAgICAgICAgc3JjPSJ7eyAkaGVyb0ZhbGxiYWNrV2VicCB9fSIKICAgICAgICAgICAgICAgIEBpZigkaGVyb0pwZyAhPT0gW10pIHNyY3NldD0ie3sgaW1wbG9kZSgnLCAnLCAkaGVyb0pwZykgfX0iIHNpemVzPSIobWF4LXdpZHRoOiA3NjhweCkgNjQwcHgsIDEwMHZ3IiBAZW5kaWYKICAgICAgICAgICAgICAgIGFsdD0iIgogICAgICAgICAgICAgICAgd2lkdGg9IjEyODAiCiAgICAgICAgICAgICAgICBoZWlnaHQ9Ijg1MyIKICAgICAgICAgICAgICAgIGZldGNocHJpb3JpdHk9ImhpZ2giCiAgICAgICAgICAgICAgICBkZWNvZGluZz0iYXN5bmMiCiAgICAgICAgICAgID4KICAgICAgICA8L3BpY3R1cmU+CiAgICA8L2Rpdj4KICAgIDxkaXYgY2xhc3M9ImxhbmRpbmctaGVyby1vdmVybGF5Ij48L2Rpdj4KICAgIDxkaXYgY2xhc3M9ImxhbmRpbmctaGVyby1ncmlke3sgYXV0aCgpLT5jaGVjaygpID8gJyBsYW5kaW5nLWhlcm8tZ3JpZC0tc29sbycgOiAnJyB9fSI+CiAgICAgICAgPGRpdiBjbGFzcz0ibGFuZGluZy1oZXJvLWNvcHkiPgogICAgICAgICAgICA8cCBjbGFzcz0ibGFuZGluZy1oZXJvLWV5ZWJyb3ciPkV2bGlsaWsgdmUgdGFuxLHFn21hIHBsYXRmb3JtdTwvcD4KICAgICAgICAgICAgPGgxPkRvxJ9ydSBpbnNhbiw8YnI+PHNwYW4gY2xhc3M9ImxhbmRpbmctaGVyby1hY2NlbnQiPmRvxJ9ydSB5ZXIuPC9zcGFuPjwvaDE+CiAgICAgICAgICAgIDxwIGNsYXNzPSJsYW5kaW5nLWhlcm8tbGVhZCI+CiAgICAgICAgICAgICAgICBDaWRkaSBpbGnFn2tpIGFyYXlhbiB5ZXRpxZ9raW5sZXIgacOnaW4gZ8O8dmVubGksIHNheWfEsWzEsSB2ZSBtb2Rlcm4gYmlyIHRhbsSxxZ9tYSBvcnRhbcSxLgogICAgICAgICAgICAgICAgR8O2bsO8bCBLw7ZwcsO8c8O8IGlsZSBhbmxhbWzEsSBiYcSfbGFyIGt1ci4KICAgICAgICAgICAgPC9wPgogICAgICAgICAgICA8dWwgY2xhc3M9ImxhbmRpbmctaGVyby1waWxscyI+CiAgICAgICAgICAgICAgICA8bGk+CiAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9ImxhbmRpbmctaGVyby1waWxsLWljb24iPkBpbmNsdWRlKCdwYXJ0aWFscy50aGVtZS1pY29uJywgWydpY29uJyA9PiAnaGVhcnQnXSk8L3NwYW4+CiAgICAgICAgICAgICAgICAgICAgQ2lkZGkgw5x5ZWxpawogICAgICAgICAgICAgICAgPC9saT4KICAgICAgICAgICAgICAgIDxsaT4KICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz0ibGFuZGluZy1oZXJvLXBpbGwtaWNvbiI+QGluY2x1ZGUoJ3BhcnRpYWxzLnRoZW1lLWljb24nLCBbJ2ljb24nID0+ICdzaGllbGQnXSk8L3NwYW4+CiAgICAgICAgICAgICAgICAgICAgJTEwMCBHw7x2ZW5saQogICAgICAgICAgICAgICAgPC9saT4KICAgICAgICAgICAgICAgIDxsaT4KICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz0ibGFuZGluZy1oZXJvLXBpbGwtaWNvbiI+QGluY2x1ZGUoJ3BhcnRpYWxzLnRoZW1lLWljb24nLCBbJ2ljb24nID0+ICdzcGFya2xlcyddKTwvc3Bhbj4KICAgICAgICAgICAgICAgICAgICBFxZ9sZcWfbWUgT2Rha2zEsQogICAgICAgICAgICAgICAgPC9saT4KICAgICAgICAgICAgPC91bD4KICAgICAgICAgICAgQGd1ZXN0CiAgICAgICAgICAgIDxkaXYgY2xhc3M9ImxhbmRpbmctaGVyby1hY3Rpb25zIGxhbmRpbmctaGVyby1hY3Rpb25zLS1pbmxpbmUiPgogICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgcm91dGUoJ3JlZ2lzdGVyJykgfX0iIGNsYXNzPSJidG4gYnRuLXByaW1hcnkiPsOcY3JldHNpeiDDnHllIE9sPC9hPgogICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgcm91dGUoJ2xvZ2luJykgfX0iIGNsYXNzPSJidG4gYnRuLWdob3N0Ij5HaXJpxZ8gWWFwPC9hPgogICAgICAgICAgICA8L2Rpdj4KICAgICAgICAgICAgPGRpdiBjbGFzcz0ibGFuZGluZy10cnVzdC13cmFwIj4KICAgICAgICAgICAgICAgIEBpbmNsdWRlKCdwYXJ0aWFscy50cnVzdC1iYWRnZXMnKQogICAgICAgICAgICA8L2Rpdj4KICAgICAgICAgICAgQGVuZGd1ZXN0CiAgICAgICAgICAgIEBhdXRoCiAgICAgICAgICAgIDxkaXYgY2xhc3M9ImxhbmRpbmctaGVyby1hY3Rpb25zIj4KICAgICAgICAgICAgICAgIDxhIGhyZWY9Int7IHJvdXRlKCdmZWVkJykgfX0iIGNsYXNzPSJidG4gYnRuLXByaW1hcnkiPkFrxLHFn2EgR2l0PC9hPgogICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgcm91dGUoJ21lc3NhZ2VzLmluZGV4JykgfX0iIGNsYXNzPSJidG4gYnRuLWdob3N0Ij5NZXNhamxhcsSxbTwvYT4KICAgICAgICAgICAgPC9kaXY+CiAgICAgICAgICAgIEBlbmRhdXRoCiAgICAgICAgPC9kaXY+CgogICAgICAgIEBndWVzdAogICAgICAgIDxkaXYgY2xhc3M9ImxhbmRpbmctaGVyby12aXN1YWwiPgogICAgICAgICAgICA8YXNpZGUgY2xhc3M9ImxhbmRpbmctc2lnbnVwIGdsYXNzLWNhcmQiPgogICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9ImxhbmRpbmctc2lnbnVwLWJhZGdlIj5UYW1hbWVuIMO8Y3JldHNpejwvc3Bhbj4KICAgICAgICAgICAgICAgIDxoMj5IZW1lbiDDnGNyZXRzaXogw5x5ZSBPbDwvaDI+CiAgICAgICAgICAgICAgICA8cCBjbGFzcz0ibGFuZGluZy1zaWdudXAtc3ViIj5Qcm9maWxpbmkgb2x1xZ90dXIsIHRhbsSxxZ9tYXlhIGJhxZ9sYS48L3A+CiAgICAgICAgICAgICAgICA8dWwgY2xhc3M9ImxhbmRpbmctc2lnbnVwLWJlbmVmaXRzIj4KICAgICAgICAgICAgICAgICAgICA8bGk+w5xjcmV0c2l6IGthecSxdDwvbGk+CiAgICAgICAgICAgICAgICAgICAgPGxpPkfDvHZlbmxpIG1lc2FqbGHFn21hPC9saT4KICAgICAgICAgICAgICAgICAgICA8bGk+UHJvZmlsIHZlIGtlxZ9pZjwvbGk+CiAgICAgICAgICAgICAgICA8L3VsPgogICAgICAgICAgICAgICAgPGEgaHJlZj0ie3sgcm91dGUoJ3JlZ2lzdGVyJykgfX0iIGNsYXNzPSJidG4gYnRuLXByaW1hcnkgYnRuLWZ1bGwiPsOcY3JldHNpeiDDnHllIE9sPC9hPgogICAgICAgICAgICAgICAgPHAgY2xhc3M9ImxhbmRpbmctc2lnbnVwLWxvZ2luIj5aYXRlbiBoZXNhYsSxbiB2YXIgbcSxPyA8YSBocmVmPSJ7eyByb3V0ZSgnbG9naW4nKSB9fSI+R2lyacWfIFlhcDwvYT48L3A+CiAgICAgICAgICAgIDwvYXNpZGU+CiAgICAgICAgPC9kaXY+CiAgICAgICAgQGVuZGd1ZXN0CiAgICA8L2Rpdj4KPC9zZWN0aW9uPgoKQGluY2x1ZGUoJ3BhcnRpYWxzLmhvbWVwYWdlLWJvZHknKQpAZW5kc2VjdGlvbgoKQGlzc2V0KCRqc29uTGQpCkBwdXNoKCdsZC1qc29uJykKICAgIEBpbmNsdWRlKCdwYXJ0aWFscy5qc29uLWxkJywgWydzY2hlbWEnID0+ICRqc29uTGRdKQpAZW5kcHVzaApAZW5kaXNzZXQK", "resources/views/partials/seo-head.blade.php": "QHBocAogICAgJHNldHRpbmdzID0gYXBwKFxBcHBcU2VydmljZXNcU2l0ZVNldHRpbmdzU2VydmljZTo6Y2xhc3MpOwogICAgJHNlbyA9IFxBcHBcU3VwcG9ydFxTZW9IZWxwZXI6OmFsbCgpOwogICAgJGJyYW5kID0gKHN0cmluZykgJHNldHRpbmdzLT5nZXQoJ3NpdGVfbmFtZScsIGNvbmZpZygnYXBwLm5hbWUnLCAnR8O2bsO8bCBLw7ZwcsO8c8O8JykpOwogICAgJHNpdGVVcmwgPSBydHJpbSgoc3RyaW5nKSAkc2V0dGluZ3MtPmdldCgnc2l0ZV91cmwnLCBjb25maWcoJ2FwcC51cmwnLCAnaHR0cHM6Ly93d3cuZ29udWxrb3BydXN1LmNvbScpKSwgJy8nKTsKICAgICRwYWdlVGl0bGUgPSB0cmltKCRfX2Vudi0+eWllbGRDb250ZW50KCd0aXRsZScpKTsKICAgICRzZW9UaXRsZSA9IChzdHJpbmcpICgkc2VvWyd0aXRsZSddID8/ICcnKTsKICAgICRmdWxsVGl0bGUgPSAkcGFnZVRpdGxlICE9PSAnJyAmJiAhIHN0cl9jb250YWlucygkcGFnZVRpdGxlLCAkYnJhbmQpCiAgICAgICAgPyAkcGFnZVRpdGxlCiAgICAgICAgOiAoJHNlb1RpdGxlICE9PSAnJyA/ICRzZW9UaXRsZS4nIOKAlCAnLiRicmFuZCA6ICRicmFuZCk7CiAgICAkZGVzY3JpcHRpb24gPSAoc3RyaW5nKSAoJHNlb1snZGVzY3JpcHRpb24nXSA/PyAkc2V0dGluZ3MtPmdldCgnZGVmYXVsdF9kZXNjcmlwdGlvbicsICcnKSk7CiAgICAka2V5d29yZHMgPSAoc3RyaW5nKSAoJHNlb1sna2V5d29yZHMnXSA/PyAkc2V0dGluZ3MtPmdldCgnZGVmYXVsdF9rZXl3b3JkcycsICcnKSk7CiAgICAkY2Fub25pY2FsID0gKHN0cmluZykgKCRzZW9bJ2Nhbm9uaWNhbCddID8/IHVybCgpLT5jdXJyZW50KCkpOwogICAgJG9nSW1hZ2UgPSAoc3RyaW5nKSAoJHNlb1snb2dJbWFnZSddID8/ICRzZXR0aW5ncy0+Z2V0KCdvZ19pbWFnZV91cmwnLCAkc2l0ZVVybC4nL2ltYWdlcy9vZy1kZWZhdWx0LmpwZycpKTsKICAgICRvZ1R5cGUgPSAoc3RyaW5nKSAoJHNlb1snb2dUeXBlJ10gPz8gJ3dlYnNpdGUnKTsKICAgICRub2luZGV4ID0gKGJvb2wpICgkc2VvWydub2luZGV4J10gPz8gZmFsc2UpOwogICAgJHJvYm90c0luZGV4ID0gJHNldHRpbmdzLT5ib29sKCdyb2JvdHNfaW5kZXgnLCB0cnVlKTsKICAgICR0d2l0dGVySGFuZGxlID0gbHRyaW0oKHN0cmluZykgJHNldHRpbmdzLT5nZXQoJ3R3aXR0ZXJfaGFuZGxlJywgJ0Bnb251bGtvcHJ1c3Vjb20nKSwgJ0AnKTsKICAgICRnb29nbGVWZXJpZmljYXRpb24gPSB0cmltKChzdHJpbmcpICRzZXR0aW5ncy0+Z2V0KCdnb29nbGVfc2l0ZV92ZXJpZmljYXRpb24nLCAnJykpOwogICAgJGJpbmdWZXJpZmljYXRpb24gPSB0cmltKChzdHJpbmcpICRzZXR0aW5ncy0+Z2V0KCdiaW5nX3NpdGVfdmVyaWZpY2F0aW9uJywgJycpKTsKICAgICRsYW5ndWFnZXMgPSBbJ3RyJywgJ2VuJywgJ2RlJywgJ2ZyJywgJ2hpJ107CiAgICAkY3VycmVudFVybCA9IHVybCgpLT5jdXJyZW50KCk7CkBlbmRwaHAKQGlmKCRzZW9UaXRsZSAhPT0gJycgJiYgJHBhZ2VUaXRsZSAhPT0gJycgJiYgJHBhZ2VUaXRsZSAhPT0gJGZ1bGxUaXRsZSkKPHRpdGxlPnt7ICRmdWxsVGl0bGUgfX08L3RpdGxlPgpAZW5kaWYKPG1ldGEgbmFtZT0iZGVzY3JpcHRpb24iIGNvbnRlbnQ9Int7ICRkZXNjcmlwdGlvbiB9fSI+CkBpZigka2V5d29yZHMgIT09ICcnKQo8bWV0YSBuYW1lPSJrZXl3b3JkcyIgY29udGVudD0ie3sgJGtleXdvcmRzIH19Ij4KQGVuZGlmCjxtZXRhIG5hbWU9ImF1dGhvciIgY29udGVudD0ie3sgJGJyYW5kIH19Ij4KQGlmKCRnb29nbGVWZXJpZmljYXRpb24gIT09ICcnKQo8bWV0YSBuYW1lPSJnb29nbGUtc2l0ZS12ZXJpZmljYXRpb24iIGNvbnRlbnQ9Int7ICRnb29nbGVWZXJpZmljYXRpb24gfX0iPgpAZW5kaWYKQGlmKCRiaW5nVmVyaWZpY2F0aW9uICE9PSAnJykKPG1ldGEgbmFtZT0ibXN2YWxpZGF0ZS4wMSIgY29udGVudD0ie3sgJGJpbmdWZXJpZmljYXRpb24gfX0iPgpAZW5kaWYKPG1ldGEgbmFtZT0icm9ib3RzIiBjb250ZW50PSJ7eyAoJG5vaW5kZXggfHwgISAkcm9ib3RzSW5kZXgpID8gJ25vaW5kZXgsIG5vZm9sbG93JyA6ICdpbmRleCwgZm9sbG93LCBtYXgtaW1hZ2UtcHJldmlldzpsYXJnZSwgbWF4LXNuaXBwZXQ6LTEsIG1heC12aWRlby1wcmV2aWV3Oi0xJyB9fSI+CjxsaW5rIHJlbD0iY2Fub25pY2FsIiBocmVmPSJ7eyAkY2Fub25pY2FsIH19Ij4KPG1ldGEgbmFtZT0iZm9ybWF0LWRldGVjdGlvbiIgY29udGVudD0idGVsZXBob25lPW5vIj4KPG1ldGEgbmFtZT0ibW9iaWxlLXdlYi1hcHAtY2FwYWJsZSIgY29udGVudD0ieWVzIj4KPG1ldGEgbmFtZT0iYXBwbGUtbW9iaWxlLXdlYi1hcHAtY2FwYWJsZSIgY29udGVudD0ieWVzIj4KPG1ldGEgbmFtZT0iYXBwbGUtbW9iaWxlLXdlYi1hcHAtc3RhdHVzLWJhci1zdHlsZSIgY29udGVudD0iZGVmYXVsdCI+CjxtZXRhIG5hbWU9ImFwcGxlLW1vYmlsZS13ZWItYXBwLXRpdGxlIiBjb250ZW50PSJ7eyAkYnJhbmQgfX0iPgo8bWV0YSBwcm9wZXJ0eT0ib2c6dGl0bGUiIGNvbnRlbnQ9Int7ICRmdWxsVGl0bGUgfX0iPgo8bWV0YSBwcm9wZXJ0eT0ib2c6ZGVzY3JpcHRpb24iIGNvbnRlbnQ9Int7ICRkZXNjcmlwdGlvbiB9fSI+CjxtZXRhIHByb3BlcnR5PSJvZzppbWFnZSIgY29udGVudD0ie3sgJG9nSW1hZ2UgfX0iPgo8bWV0YSBwcm9wZXJ0eT0ib2c6aW1hZ2U6d2lkdGgiIGNvbnRlbnQ9IjEyMDAiPgo8bWV0YSBwcm9wZXJ0eT0ib2c6aW1hZ2U6aGVpZ2h0IiBjb250ZW50PSI2MzAiPgo8bWV0YSBwcm9wZXJ0eT0ib2c6aW1hZ2U6YWx0IiBjb250ZW50PSJ7eyBcSWxsdW1pbmF0ZVxTdXBwb3J0XFN0cjo6bGltaXQoJGRlc2NyaXB0aW9uLCAxMjApIH19Ij4KPG1ldGEgcHJvcGVydHk9Im9nOnVybCIgY29udGVudD0ie3sgJGNhbm9uaWNhbCB9fSI+CjxtZXRhIHByb3BlcnR5PSJvZzp0eXBlIiBjb250ZW50PSJ7eyAkb2dUeXBlIH19Ij4KPG1ldGEgcHJvcGVydHk9Im9nOmxvY2FsZSIgY29udGVudD0idHJfVFIiPgo8bWV0YSBwcm9wZXJ0eT0ib2c6c2l0ZV9uYW1lIiBjb250ZW50PSJ7eyAkYnJhbmQgfX0iPgpAZm9yZWFjaChbJ2VuX1VTJywgJ2RlX0RFJywgJ2ZyX0ZSJywgJ2hpX0lOJ10gYXMgJGFsdExvY2FsZSkKPG1ldGEgcHJvcGVydHk9Im9nOmxvY2FsZTphbHRlcm5hdGUiIGNvbnRlbnQ9Int7ICRhbHRMb2NhbGUgfX0iPgpAZW5kZm9yZWFjaAo8bWV0YSBuYW1lPSJ0d2l0dGVyOmNhcmQiIGNvbnRlbnQ9InN1bW1hcnlfbGFyZ2VfaW1hZ2UiPgo8bWV0YSBuYW1lPSJ0d2l0dGVyOnRpdGxlIiBjb250ZW50PSJ7eyAkZnVsbFRpdGxlIH19Ij4KPG1ldGEgbmFtZT0idHdpdHRlcjpkZXNjcmlwdGlvbiIgY29udGVudD0ie3sgJGRlc2NyaXB0aW9uIH19Ij4KPG1ldGEgbmFtZT0idHdpdHRlcjppbWFnZSIgY29udGVudD0ie3sgJG9nSW1hZ2UgfX0iPgo8bWV0YSBuYW1lPSJ0d2l0dGVyOmltYWdlOmFsdCIgY29udGVudD0ie3sgXElsbHVtaW5hdGVcU3VwcG9ydFxTdHI6OmxpbWl0KCRkZXNjcmlwdGlvbiwgMTIwKSB9fSI+CkBpZigkdHdpdHRlckhhbmRsZSAhPT0gJycpCjxtZXRhIG5hbWU9InR3aXR0ZXI6c2l0ZSIgY29udGVudD0iQHt7ICR0d2l0dGVySGFuZGxlIH19Ij4KQGVuZGlmCkBmb3JlYWNoKCRsYW5ndWFnZXMgYXMgJGxhbmcpCjxsaW5rIHJlbD0iYWx0ZXJuYXRlIiBocmVmbGFuZz0ie3sgJGxhbmcgfX0iIGhyZWY9Int7ICRsYW5nID09PSAndHInID8gJGN1cnJlbnRVcmwgOiAkY3VycmVudFVybC4oc3RyX2NvbnRhaW5zKCRjdXJyZW50VXJsLCAnPycpID8gJyYnIDogJz8nKS4nbGFuZz0nLiRsYW5nIH19Ij4KQGVuZGZvcmVhY2gKPGxpbmsgcmVsPSJhbHRlcm5hdGUiIGhyZWZsYW5nPSJ4LWRlZmF1bHQiIGhyZWY9Int7ICRjdXJyZW50VXJsIH19Ij4KPGxpbmsgcmVsPSJkbnMtcHJlZmV0Y2giIGhyZWY9Ii8vd3d3Lmdvb2dsZXRhZ21hbmFnZXIuY29tIj4KPGxpbmsgcmVsPSJkbnMtcHJlZmV0Y2giIGhyZWY9Ii8vd3d3Lmdvb2dsZS1hbmFseXRpY3MuY29tIj4KPGxpbmsgcmVsPSJkbnMtcHJlZmV0Y2giIGhyZWY9Ii8vZm9udHMuZ29vZ2xlYXBpcy5jb20iPgo8bGluayByZWw9ImRucy1wcmVmZXRjaCIgaHJlZj0iLy9mb250cy5nc3RhdGljLmNvbSI+Cg==", "resources/views/partials/json-ld.blade.php": "QHBocAogICAgJHNjaGVtYSA9ICRzY2hlbWEgPz8gW107CkBlbmRwaHAKQGlmKCFlbXB0eSgkc2NoZW1hKSkKPHNjcmlwdCB0eXBlPSJhcHBsaWNhdGlvbi9sZCtqc29uIj57ISEganNvbl9lbmNvZGUoJHNjaGVtYSwgSlNPTl9VTkVTQ0FQRURfVU5JQ09ERSB8IEpTT05fVU5FU0NBUEVEX1NMQVNIRVMpICEhfTwvc2NyaXB0PgpAZW5kaWYK"}
JSON, true);
        $written = [];
        foreach ($payload as $rel => $b64) {
            $path = base_path($rel);
            \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($path));
            \Illuminate\Support\Facades\File::put($path, base64_decode($b64));
            $written[] = $rel.' ('.filesize($path).' bytes)';
        }
        $htaccess = <<<'HTA'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{HTTP_HOST} ^gonulkoprusu\.com$ [NC]
    RewriteRule ^ https://www.gonulkoprusu.com%{REQUEST_URI} [L,R=301]
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTA;
        \Illuminate\Support\Facades\File::put(public_path('.htaccess'), $htaccess);
        $written[] = 'public/.htaccess ('.filesize(public_path('.htaccess')).' bytes)';
        try { Artisan::call('view:clear'); } catch (\Throwable $e) {}
        return response("Restored:\n".implode("\n", $written)."\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    });
}

Route::get('/kvkk', [LegalPageController::class, 'kvkk'])->name('kvkk');
Route::get('/kullanim-kosullari', [LegalPageController::class, 'terms'])->name('terms');
Route::get('/sikayet-ve-engelleme', [LegalPageController::class, 'complaints'])->name('complaints');
Route::get('/guvenli-tanisma', [LegalPageController::class, 'safeMeeting'])->name('safe-meeting');
Route::get('/destek', [SupportPageController::class, 'show'])->name('support');
Route::post('/destek', [SupportPageController::class, 'store'])->middleware('throttle:6,1,support')->name('support.store');
Route::get('/sehir/{slug}', [CitySeoPageController::class, 'show'])->name('city.seo')->where('slug', '[a-z0-9\-]+');
Route::get('/hakkimizda', [LegalPageController::class, 'about'])->name('about');
Route::get('/blog', [LegalPageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [LegalPageController::class, 'blogShow'])->name('blog.show')->where('slug', '[a-z0-9\-]+');
Route::get('/sss', [LegalPageController::class, 'sss'])->name('sss');
Route::post('/setup/seo-blog-faq-sync', function () {
    if (! hash_equals((string) config('services.seo.sync_key', env('SEO_SYNC_KEY', 'gk-seo-sync-2026')), (string) request('key', ''))) {
        abort(403);
    }

    $payload = request('payload');
    if (! is_array($payload)) {
        return response()->json(['ok' => false, 'message' => 'Geçersiz payload.'], 422);
    }

    if (empty($payload['blog_posts']) && empty($payload['faq_items'])) {
        return response()->json(['ok' => false, 'message' => 'Yayınlanacak içerik bulunamadı.'], 422);
    }

    try {
        \App\Http\Controllers\Web\LegalPageController::storePublishedBlogFaq($payload);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'message' => 'Kayıt hatası: '.$e->getMessage()], 500);
    }

    return response()->json([
        'ok' => true,
        'message' => 'Blog / SSS içeriği kaydedildi.',
        'blog_count' => count($payload['blog_posts'] ?? []),
        'faq_count' => count($payload['faq_items'] ?? []),
    ]);
})->withoutMiddleware([
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
])->middleware('throttle:10,1,seo-blog-faq-sync');

// ========== Arama ==========
Route::get('/ara', [SearchController::class, 'index'])->name('search');
Route::get('/ara/oneriler', [SearchController::class, 'suggest'])->middleware('throttle:60,1,search-suggest')->name('search.suggest');

// ========== SEO Routes ==========
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $robotsFile = base_path('../public_html/robots.txt');
    if (is_file($robotsFile)) {
        return response(file_get_contents($robotsFile), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=86400')
            ->header('X-Robots-Tag', 'noindex');
    }
    $lines = [
        '# robots.txt - Gonul Koprusu',
        '# https://www.gonulkoprusu.com',
        '',
        'User-agent: *',
        'Allow: /',
        'Allow: /hakkimizda',
        'Allow: /gizlilik-politikasi',
        'Allow: /kvkk',
        'Allow: /kullanim-kosullari',
        'Allow: /sikayet-ve-engelleme',
        'Allow: /guvenli-tanisma',
        'Allow: /blog',
        'Allow: /sss',
        'Allow: /destek',
        'Allow: /sehir/',
        'Allow: /register',
        'Allow: /login',
        'Allow: /locations/',
        'Allow: /ara',
        'Allow: /users/',
        '',
        '# Korumali alanlar',
        'Disallow: /api/',
        'Disallow: /admin/',
        'Disallow: /adminlogin/',
        'Disallow: /setup/',
        'Disallow: /broadcasting/',
        'Disallow: /live/',
        'Disallow: /profile',
        'Disallow: /feed',
        'Disallow: /messages/',
        'Disallow: /notifications/',
        'Disallow: /premium',
        'Disallow: /sanctum/',
        'Disallow: /storage/',
        'Disallow: /_debugbar/',
        'Disallow: /vendor/',
        'Disallow: /marketing/',
        'Disallow: /ara/oneriler',
        '',
        'Crawl-delay: 1',
        '',
        'Sitemap: https://www.gonulkoprusu.com/sitemap.xml',
    ];
    return response(implode("\n", $lines), 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=86400')
        ->header('X-Robots-Tag', 'noindex');
})->name('robots');


// Auth pages
Route::get('/register', [AuthPageController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthPageController::class, 'register'])->middleware('throttle:10,1,register');
Route::get('/login', [AuthPageController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthPageController::class, 'login'])->middleware('throttle:12,1,login');
Route::get('/forgot-password', [AuthPageController::class, 'forgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthPageController::class, 'sendPasswordResetLink'])->middleware('throttle:6,1,password-email')->name('password.email');
Route::get('/reset-password/{token}', [AuthPageController::class, 'resetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthPageController::class, 'resetPassword'])->middleware('throttle:6,1,password-reset')->name('password.update');
Route::post('/logout', [AuthPageController::class, 'logout'])->name('logout');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/google/complete', [GoogleAuthController::class, 'completeForm'])->name('auth.google.complete');
Route::post('/auth/google/complete', [GoogleAuthController::class, 'complete'])->middleware('throttle:10,1,google-complete');

// Authenticated web pages
Route::middleware(['auth', 'locale'])->group(function () {
    Broadcast::routes();

    Route::get('/feed', [FeedPageController::class, 'index'])->name('feed');
    Route::post('/posts', [PostPageController::class, 'store'])->middleware('throttle:20,1,posts-store')->name('posts.store');
    Route::delete('/posts/{post}', [PostPageController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [PostPageController::class, 'toggleLike'])->middleware('throttle:60,1,posts-like')->name('posts.like');
    Route::post('/stories', [StoryPageController::class, 'store'])->middleware('throttle:15,1,stories-store')->name('stories.store');
    Route::delete('/stories/{story}', [StoryPageController::class, 'destroy'])->name('stories.destroy');
    Route::get('/profile', [ProfilePageController::class, 'index'])->name('profile');
    Route::get('/davet', [ReferralPageController::class, 'index'])->name('referral');
    Route::put('/profile', [ProfilePageController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfilePageController::class, 'uploadPhoto'])->name('profile.photo');
    Route::get('/profile/locale/{locale}', [ProfilePageController::class, 'switchLocale'])
        ->where('locale', 'tr|en|de|fr|hi')
        ->name('profile.locale');
    Route::post('/profile/locale', [ProfilePageController::class, 'updateLocale'])->name('profile.locale.post');
    Route::get('/users', [UserProfilePageController::class, 'index'])->name('users.index');
    Route::get('/users/{username}', [UserProfilePageController::class, 'show'])->name('users.show');
    Route::post('/users/{username}/report', [UserProfilePageController::class, 'report'])->middleware('throttle:10,1,users-report')->name('users.report');
    Route::post('/users/{username}/block', [UserProfilePageController::class, 'block'])->middleware('throttle:30,1,users-block')->name('users.block');
    Route::delete('/users/{username}/block', [UserProfilePageController::class, 'unblock'])->middleware('throttle:30,1,users-unblock')->name('users.unblock');
    Route::get('/locations/{country}/{city}/{district?}', [LocationUsersPageController::class, 'index'])
        ->name('locations.users')
        ->where(['country' => '[^/]+', 'city' => '[^/]+', 'district' => '[^/]*']);
    Route::get('/premium', [PremiumPageController::class, 'index'])->name('premium');
    Route::get('/notifications', [NotificationPageController::class, 'index'])->name('notifications.index');
    Route::get('/live/sync', [LiveSyncController::class, 'sync'])->middleware('throttle:120,1,live-sync')->name('live.sync');
    Route::get('/notifications/badge-counts', [NotificationPageController::class, 'badgeCounts'])->middleware('throttle:120,1,notifications-badges')->name('notifications.badge-counts');
    Route::get('/notifications/poll', [NotificationPageController::class, 'poll'])->middleware('throttle:120,1,notifications-poll')->name('notifications.poll');
    Route::get('/messages', [MessagePageController::class, 'index'])->name('messages.index');
    Route::get('/messages/inbox/poll', [MessagePageController::class, 'inboxPoll'])->middleware('throttle:120,1,messages-inbox-poll')->name('messages.inbox.poll');
    Route::get('/messages/{username}', [MessagePageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{username}', [MessagePageController::class, 'store'])->middleware('throttle:30,1,messages-store')->name('messages.store');
    Route::post('/messages/{username}/typing', [MessagePageController::class, 'pingTyping'])->middleware('throttle:40,1,messages-typing-ping')->name('messages.typing.ping');
    Route::get('/messages/{username}/typing', [MessagePageController::class, 'typingStatus'])->middleware('throttle:60,1,messages-typing-status')->name('messages.typing.status');
    Route::get('/messages/{username}/poll', [MessagePageController::class, 'poll'])->middleware('throttle:120,1,messages-poll')->name('messages.poll');
    Route::delete('/messages/{username}/{message}', [MessagePageController::class, 'destroy'])->middleware('throttle:60,1,messages-destroy')->name('messages.destroy');
    Route::delete('/messages/{username}', [MessagePageController::class, 'clearConversation'])->middleware('throttle:20,1,messages-clear')->name('messages.clear');
    Route::post('/messages/{username}/block', [MessagePageController::class, 'block'])->middleware('throttle:30,1,messages-block')->name('messages.block');
});

Route::redirect('/admin', 'https://admin.gonulkoprusu.com/login', 301);
Route::any('/admin/{path}', fn (string $path) => redirect('https://admin.gonulkoprusu.com/'.ltrim($path, '/'), 301))
    ->where('path', '.*');

Route::redirect('/adminlogin', 'https://admin.gonulkoprusu.com/login', 301);
Route::any('/adminlogin/{path}', fn (string $path) => redirect('https://admin.gonulkoprusu.com/'.ltrim($path, '/'), 301))
    ->where('path', '.*');

if (is_file(app_path('Http/Controllers/Admin/AdminAuthController.php')) && ! \App\Support\AdminApp::isSubdomainRequest()) {
    Route::prefix('adminlogin')->group(base_path('routes/adminlogin.php'));
}
