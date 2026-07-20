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
use App\Http\Controllers\Web\SeoPillarPageController;
use App\Http\Controllers\Web\SuccessStoriesController;
use App\Http\Controllers\Web\CampaignLandingController;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

$gkHttpHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
if ($gkHttpHost === 'www.gonulkoprusu.com') {
    header('Location: https://gonulkoprusu.com'.($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
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

// UTM / ref: tüm public sayfalarda session'a yaz (şehir, IG, ads → kayıt yolculuğu)
try {
    if (! app()->runningInConsole() && class_exists(\App\Services\UserAttributionService::class)) {
        app(\App\Services\UserAttributionService::class)->captureFromRequest(request());
    }
} catch (\Throwable) {
    //
}

// Public landing
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/gizlilik-politikasi', [LegalPageController::class, 'privacy'])->name('privacy');
Route::redirect('/gizlilik-sozlesmesi', '/gizlilik-politikasi', 301);

if (class_exists(\App\Http\Controllers\Web\SetupController::class)) {
    Route::get('/setup/cpanel', [\App\Http\Controllers\Web\SetupController::class, 'cpanel']);
    Route::get('/setup/performance', [\App\Http\Controllers\Web\SetupController::class, 'performance']);
    Route::get('/setup/deploy-sync', [\App\Http\Controllers\Web\SetupController::class, 'deploySync']);
    // LiteWeight fallback: SetupController opcache stale olsa bile çalışır
    Route::get('/setup/deploy-sync-lite', function () {
        if (request('key') !== 'gk-deploy-sync-2026') {
            abort(403);
        }
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        $base = base_path();
        foreach (glob($base.'/storage/framework/views/*.php') ?: [] as $file) {
            @unlink($file);
        }
        foreach (['view:clear', 'config:clear', 'route:clear'] as $command) {
            try {
                \Illuminate\Support\Facades\Artisan::call($command);
            } catch (\Throwable $e) {
            }
        }
        $lines = ['deploy-sync-lite', 'base='.$base];
        $lines[] = 'SetupController bytes='.(is_file($base.'/app/Http/Controllers/Web/SetupController.php') ? filesize($base.'/app/Http/Controllers/Web/SetupController.php') : 0);
        $log = $base.'/storage/logs/laravel.log';
        if (is_file($log)) {
            $fp = @fopen($log, 'rb');
            if ($fp) {
                @fseek($fp, max(0, (int) filesize($log) - 8192));
                $lines[] = '--- log ---';
                $lines[] = trim((string) @stream_get_contents($fp));
                @fclose($fp);
            }
        }
        $lines[] = 'OK';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
            'X-LiteSpeed-Purge' => '*',
        ]);
    });
    Route::get('/setup/notifications', [\App\Http\Controllers\Web\SetupController::class, 'notifications']);
    Route::match(['get', 'post'], '/setup/fcm', [\App\Http\Controllers\Web\SetupController::class, 'fcm']);
    Route::match(['get', 'post'], '/setup/laravel-update', [\App\Http\Controllers\Web\SetupController::class, 'laravelUpdate']);
    Route::get('/setup/email-logs', [\App\Http\Controllers\Web\SetupController::class, 'emailLogs']);
    Route::get('/setup/hobbies', [\App\Http\Controllers\Web\SetupController::class, 'hobbies']);
    Route::get('/setup/locale', [\App\Http\Controllers\Web\SetupController::class, 'locale']);
    Route::get('/setup/profile-fields', [\App\Http\Controllers\Web\SetupController::class, 'profileFields']);
    Route::get('/setup/delete-users', [\App\Http\Controllers\Web\SetupController::class, 'deleteUsers']);
    Route::get('/setup/messages', [\App\Http\Controllers\Web\SetupController::class, 'messagesSchema']);
    Route::get('/setup/cron', [\App\Http\Controllers\Web\SetupController::class, 'cron']);
    Route::get('/setup/growth', [\App\Http\Controllers\Web\SetupController::class, 'growth']);
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

        if (function_exists('opcache_reset')) {
            @opcache_reset();
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
            'X-LiteSpeed-Purge' => '*',
        ]);
    });

    Route::get('/setup/test-login', function () {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $as = (string) request('as', 'user');
        $query = \App\Models\User::query()->where('is_banned', false);

        if ($as === 'admin') {
            $query->where('role', 'admin');
        } elseif ($as === 'premium') {
            $query->where('role', 'user')->where('gender', 'male');
        } else {
            $query->where('role', 'user');
        }

        $user = $query->orderByDesc('id')->first();
        if (! $user) {
            return response("no user\n", 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        \Illuminate\Support\Facades\Auth::login($user, true);
        request()->session()->save();

        $cookieName = config('session.cookie');
        $sessionId = request()->session()->getId();

        if (request()->boolean('redirect')) {
            return redirect('/feed');
        }

        return response()->json([
            'ok' => true,
            'id' => $user->id,
            'username' => $user->username,
            'gender' => $user->gender,
            'cookie_name' => $cookieName,
            'session_cookie' => $cookieName.'='.$sessionId,
        ]);
    });
    Route::match(['get', 'post'], '/setup/google-oauth', function () {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $envPath = base_path('.env');
        $clientId = trim((string) request('client_id', ''));
        $clientSecret = trim((string) request('client_secret', ''));

        if ($clientId !== '' && $clientSecret !== '' && is_writable($envPath)) {
            $env = is_file($envPath) ? file_get_contents($envPath) : '';
            $env = preg_replace('/^GOOGLE_CLIENT_ID=.*$/m', '', $env) ?? $env;
            $env = preg_replace('/^GOOGLE_CLIENT_SECRET=.*$/m', '', $env) ?? $env;
            $env = preg_replace('/^GOOGLE_REDIRECT_URI=.*$/m', '', $env) ?? $env;
            $env = rtrim($env)."\n\nGOOGLE_CLIENT_ID={$clientId}\nGOOGLE_CLIENT_SECRET={$clientSecret}\nGOOGLE_REDIRECT_URI=https://gonulkoprusu.com/auth/google/callback\n";
            file_put_contents($envPath, $env);

            foreach (['config:clear', 'cache:clear'] as $command) {
                try {
                    Artisan::call($command);
                } catch (\Throwable) {
                }
            }
        }

        $configured = trim((string) config('services.google.client_id', '')) !== '';
        $envConfigured = trim((string) env('GOOGLE_CLIENT_ID', '')) !== '';

        $lines = [
            'Google OAuth durumu',
            'config_client_id: '.($configured ? 'tanimli' : 'bos'),
            'env_client_id: '.($envConfigured ? 'tanimli' : 'bos'),
            'redirect_uri: '.(config('services.google.redirect') ?: route('auth.google.callback', absolute: true)),
            '',
            $configured ? "OK\n" : "Eksik: client_id ve client_secret ile ?key=...&client_id=...&client_secret=...\n",
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    });
    Route::get('/setup/profile-toolbar-css', function () {
        if (request('key') !== 'gk-cpanel-setup-2026') {
            abort(403);
        }

        $cssPath = base_path('css/app.css');
        if (! is_file($cssPath)) {
            return response("missing css/app.css\n", 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $css = file_get_contents($cssPath);
        $lines = [];

        $replacements = [
            ".profile-toolbar-row {\n    display: flex;\n    align-items: flex-start;\n    gap: 0.45rem;\n    margin-bottom: 1rem;\n}" =>
            ".profile-toolbar-row {\n    display: flex;\n    flex-direction: row;\n    flex-wrap: nowrap;\n    align-items: center;\n    gap: 0.5rem;\n    margin: 0 0 0.6rem;\n}",
            ".profile-page .profile-header {\n    display: flex;\n    gap: 1rem;\n    align-items: flex-start;\n    flex-wrap: wrap;\n    margin-bottom: 1rem;\n    padding: 1rem;" =>
            ".profile-page .profile-header {\n    display: flex;\n    gap: 1rem;\n    align-items: flex-start;\n    flex-wrap: wrap;\n    margin-bottom: 0.65rem;\n    padding: 0.85rem 0.9rem;",
            "@media (max-width: 520px) {\n    .profile-toolbar-row { flex-wrap: wrap; }\n    .profile-settings--toolbar { flex: 1 1 100%; }\n    .profile-language-dropdown { margin-left: auto; }" =>
            "@media (max-width: 520px) {\n    .profile-toolbar-row { flex-wrap: nowrap; align-items: center; gap: 0.5rem; margin: 0 0 0.6rem; }\n    .profile-settings--toolbar { flex: 1 1 auto; min-width: 0; margin-bottom: 0; }\n    .profile-language-dropdown { flex: 0 0 auto; margin-left: auto; }\n    .profile-settings-toggle { width: 100%; max-width: 100%; font-size: 0.8rem; padding-left: 0.65rem; padding-right: 0.65rem; }\n    .profile-settings-toggle-label { overflow: hidden; text-overflow: ellipsis; }",
        ];

        foreach ($replacements as $old => $new) {
            if (str_contains($css, $new)) {
                $lines[] = 'already: '.substr($old, 0, 28).'...';
                continue;
            }
            if (! str_contains($css, $old)) {
                $lines[] = 'skip (not found): '.substr($old, 0, 28).'...';
                continue;
            }
            $css = str_replace($old, $new, $css);
            $lines[] = 'patched: '.substr($old, 0, 28).'...';
        }

        file_put_contents($cssPath, $css);

        return response(implode("\n", $lines)."\nOK\n", 200, [
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

}

Route::get('/kvkk', [LegalPageController::class, 'kvkk'])->name('kvkk');
Route::get('/kullanim-kosullari', [LegalPageController::class, 'terms'])->name('terms');
Route::get('/sikayet-ve-engelleme', [LegalPageController::class, 'complaints'])->name('complaints');
Route::get('/guvenli-tanisma', [LegalPageController::class, 'safeMeeting'])->name('safe-meeting');
Route::get('/destek', [SupportPageController::class, 'show'])->name('support');
Route::post('/destek', [SupportPageController::class, 'store'])->middleware('throttle:6,1,support')->name('support.store');
Route::get('/sehir/{slug}', [CitySeoPageController::class, 'show'])->name('city.seo')->where('slug', '[a-z0-9\-]+');
Route::get('/sehir/{slug}/{district}', [CitySeoPageController::class, 'showDistrict'])
    ->name('city.seo.district')
    ->where(['slug' => '[a-z0-9\-]+', 'district' => '[a-z0-9\-]+']);
Route::get('/evlilik-sitesi', [SeoPillarPageController::class, 'marriage'])->name('seo.marriage');
Route::get('/ciddi-iliski', [SeoPillarPageController::class, 'serious'])->name('seo.serious');
Route::get('/ucretsiz-tanisma-sitesi', [SeoPillarPageController::class, 'freeDating'])->name('seo.free');
Route::get('/arkadaslik-sitesi', [SeoPillarPageController::class, 'friendship'])->name('seo.friendship');
Route::get('/basari-hikayeleri', [SuccessStoriesController::class, 'show'])->name('stories');
Route::get('/kampanya', [CampaignLandingController::class, 'show'])->name('campaign.landing');
Route::get('/ads', [CampaignLandingController::class, 'show'])->name('campaign.ads');
Route::get('/davet/{code}', [ReferralPageController::class, 'show'])
    ->name('referral.invite')
    ->where('code', '[A-Za-z0-9]{4,16}');
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
Route::get('/setup/sitemap-ping', function () {
    if (request('key') !== 'gk-cpanel-setup-2026') {
        abort(403);
    }

    Cache::forget('sitemap.xml.body');
    Cache::forget('sitemap.xml.body.v2');
    Cache::forget('sitemap.xml.body.v3');
    Cache::forget('sitemap.xml.body.v4');
    Cache::forget('sitemap.xml.body.v5');

    $sitemapUrl = 'https://gonulkoprusu.com/sitemap.xml';
    $lines = ['sitemap-ping', 'sitemap='.$sitemapUrl, 'mode=slim-v5'];

    foreach ([
        'https://www.google.com/ping?sitemap='.rawurlencode($sitemapUrl),
        'https://www.bing.com/ping?sitemap='.rawurlencode($sitemapUrl),
    ] as $pingUrl) {
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
            $body = @file_get_contents($pingUrl, false, $ctx);
            $lines[] = $pingUrl.' -> '.(is_string($body) ? 'ok('.strlen($body).')' : 'fail');
        } catch (\Throwable $e) {
            $lines[] = $pingUrl.' -> '.$e->getMessage();
        }
    }

    $lines[] = 'OK';

    return response(implode("\n", $lines)."\n", 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'no-store',
    ]);
})->name('setup.sitemap-ping');
Route::get('/robots.txt', function () {
    foreach ([
        base_path('robots.txt'),
        public_path('robots.txt'),
        base_path('../public_html/robots.txt'),
    ] as $robotsFile) {
        if (is_file($robotsFile)) {
            return response(file_get_contents($robotsFile), 200)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Cache-Control', 'public, max-age=3600');
        }
    }
    $lines = [
        '# robots.txt - Gonul Koprusu',
        '# https://gonulkoprusu.com',
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
        'Allow: /sehir/',
        'Allow: /evlilik-sitesi',
        'Allow: /ciddi-iliski',
        'Allow: /ucretsiz-tanisma-sitesi',
        'Allow: /arkadaslik-sitesi',
        'Allow: /basari-hikayeleri',
        'Allow: /davet/',
        'Allow: /register',
        'Allow: /login',
        '',
        '# Giriş gerektiren / düşük değerli yollar',
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
        'Disallow: /users/',
        'Disallow: /locations/',
        'Disallow: /ara',
        'Disallow: /sanctum/',
        'Disallow: /storage/',
        'Disallow: /_debugbar/',
        'Disallow: /vendor/',
        'Disallow: /marketing/',
        'Disallow: /ara/oneriler',
        'Disallow: /kampanya',
        'Disallow: /ads',
        'Disallow: /.env',
        'Disallow: /composer.json',
        'Disallow: /.ftp-deploy-sync-state.json',
        '',
        'Sitemap: https://gonulkoprusu.com/sitemap.xml',
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
Route::post('/auth/google/prepare', [GoogleAuthController::class, 'prepare'])->middleware('throttle:20,1,google-prepare')->name('auth.google.prepare');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/google/complete', [GoogleAuthController::class, 'completeForm'])->name('auth.google.complete');
Route::post('/auth/google/complete', [GoogleAuthController::class, 'complete'])->middleware('throttle:10,1,google-complete');

// Authenticated web pages
Route::middleware(['auth', 'locale'])->group(function () {
    Broadcast::routes();

    Route::get('/feed', [FeedPageController::class, 'index'])->name('feed');
    Route::post('/posts', [PostPageController::class, 'store'])->middleware('throttle:20,1,posts-store')->name('posts.store');
    // Caption edit (profile + feed post detail dialog)
    Route::patch('/posts/{post}', [PostPageController::class, 'update'])->middleware('throttle:30,1,posts-update')->name('posts.update');
    Route::delete('/posts/{post}', [PostPageController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [PostPageController::class, 'toggleLike'])->middleware('throttle:60,1,posts-like')->name('posts.like');
    Route::post('/stories', [StoryPageController::class, 'store'])->middleware('throttle:15,1,stories-store')->name('stories.store');
    Route::delete('/stories/{story}', [StoryPageController::class, 'destroy'])->name('stories.destroy');
    Route::get('/profile', [ProfilePageController::class, 'index'])->name('profile');
    Route::get('/davet', [ReferralPageController::class, 'index'])->name('referral');
    Route::put('/profile', [ProfilePageController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfilePageController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [ProfilePageController::class, 'uploadPhoto'])->name('profile.photo');
    Route::post('/profile/gallery', [ProfilePageController::class, 'uploadGallery'])->middleware('throttle:20,1,profile-gallery')->name('profile.gallery');
    Route::delete('/profile/gallery', [ProfilePageController::class, 'destroyGallery'])->middleware('throttle:20,1,profile-gallery-del')->name('profile.gallery.destroy');
    Route::post('/profile/boost', [ProfilePageController::class, 'boost'])->middleware('throttle:5,1,profile-boost')->name('profile.boost');
    Route::get('/profile/locale/{locale}', [ProfilePageController::class, 'switchLocale'])
        ->where('locale', 'tr|en|de|fr|hi')
        ->name('profile.locale');
    Route::post('/profile/locale', [ProfilePageController::class, 'updateLocale'])->name('profile.locale.post');
    Route::get('/users', [UserProfilePageController::class, 'index'])->name('users.index');
    Route::get('/users/{username}', [UserProfilePageController::class, 'show'])->name('users.show');
    Route::post('/users/{username}/report', [UserProfilePageController::class, 'report'])->middleware('throttle:10,1,users-report')->name('users.report');
    Route::post('/users/{username}/block', [UserProfilePageController::class, 'block'])->middleware('throttle:30,1,users-block')->name('users.block');
    Route::delete('/users/{username}/block', [UserProfilePageController::class, 'unblock'])->middleware('throttle:30,1,users-unblock')->name('users.unblock');
    Route::get('/locations', [LocationUsersPageController::class, 'search'])->name('locations.search');
    Route::get('/locations/ara', [LocationUsersPageController::class, 'find'])->name('locations.find');
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
