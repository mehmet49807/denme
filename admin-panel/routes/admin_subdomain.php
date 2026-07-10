<?php

use App\Http\Controllers\Admin\AdminGithubController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

if (is_file(app_path('Http/Controllers/Admin/AdminAuthController.php'))) {
    Route::middleware('web')->group(base_path('routes/adminlogin.php'));
}

Route::get('/setup/clear-cache', function () {
    if (request('key') !== config('deploy.setup_key', 'gk-cpanel-setup-2026')) {
        abort(403);
    }

    foreach (['route:clear', 'view:clear', 'config:clear', 'cache:clear'] as $command) {
        try {
            Artisan::call($command);
        } catch (\Throwable) {
            // Hosting kısıtlarında bazı komutlar başarısız olabilir.
        }
    }

    return response("Cache temizlendi.\n", 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'no-store',
    ]);
});

if (class_exists(\App\Http\Controllers\Web\SetupController::class)) {
    Route::get('/setup/cpanel', [\App\Http\Controllers\Web\SetupController::class, 'cpanel']);
    Route::get('/setup/messages', [\App\Http\Controllers\Web\SetupController::class, 'messagesSchema']);
}

if (is_file(app_path('Http/Controllers/Admin/AdminGithubController.php'))) {
    Route::get('/setup/deploy-notify', [AdminGithubController::class, 'deployNotify']);
}

Route::redirect('/adminlogin', '/login', 301);
Route::any('/adminlogin/{path}', fn (string $path) => redirect('/'.ltrim($path, '/'), 301))
    ->where('path', '.*');
