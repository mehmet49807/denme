<?php

use App\Http\Controllers\Admin\AdminGithubController;
use App\Support\AdminApp;
use Illuminate\Support\Facades\Route;

// Ana siteyle .gonulkoprusu.com üzerinden çerez paylaşımını kes (host-only session).
AdminApp::isolateSessionCookieDomain();

if (is_file(app_path('Http/Controllers/Admin/AdminAuthController.php'))) {
    Route::middleware('web')->group(base_path('routes/adminlogin.php'));
}

if (class_exists(\App\Http\Controllers\Web\SetupController::class)) {
    // Key checks live inside SetupController; keep CSRF for browser POSTs where possible.
    // FCM machine install still needs CSRF exemption + key auth inside controller.
    Route::get('/setup/cpanel', [\App\Http\Controllers\Web\SetupController::class, 'cpanel'])
        ->middleware('throttle:30,1,admin-setup');
    Route::get('/setup/messages', [\App\Http\Controllers\Web\SetupController::class, 'messagesSchema'])
        ->middleware('throttle:30,1,admin-setup');
    Route::get('/setup/support-tickets', [\App\Http\Controllers\Web\SetupController::class, 'supportTickets'])
        ->middleware('throttle:30,1,admin-setup');
    Route::match(['get', 'post'], '/setup/fcm', [\App\Http\Controllers\Web\SetupController::class, 'fcm'])
        ->middleware('throttle:20,1,admin-setup-fcm')
        ->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    Route::match(['get', 'post'], '/setup/laravel-update', [\App\Http\Controllers\Web\SetupController::class, 'laravelUpdate'])
        ->middleware('throttle:10,1,admin-setup-update');
}

if (is_file(app_path('Http/Controllers/Admin/AdminGithubController.php'))) {
    Route::get('/setup/deploy-notify', [AdminGithubController::class, 'deployNotify'])
        ->middleware('throttle:30,1,admin-deploy-notify');
}

Route::redirect('/adminlogin', '/login', 301);
Route::any('/adminlogin/{path}', fn (string $path) => redirect('/'.ltrim($path, '/'), 301))
    ->where('path', '.*');
