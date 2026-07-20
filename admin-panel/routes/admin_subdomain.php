<?php

use App\Http\Controllers\Admin\AdminGithubController;
use Illuminate\Support\Facades\Route;

if (is_file(app_path('Http/Controllers/Admin/AdminAuthController.php'))) {
    Route::middleware('web')->group(base_path('routes/adminlogin.php'));
}

if (class_exists(\App\Http\Controllers\Web\SetupController::class)) {
    Route::get('/setup/cpanel', [\App\Http\Controllers\Web\SetupController::class, 'cpanel']);
    Route::get('/setup/messages', [\App\Http\Controllers\Web\SetupController::class, 'messagesSchema']);
    Route::match(['get', 'post'], '/setup/fcm', [\App\Http\Controllers\Web\SetupController::class, 'fcm'])
        ->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    Route::match(['get', 'post'], '/setup/laravel-update', [\App\Http\Controllers\Web\SetupController::class, 'laravelUpdate']);
}

if (is_file(app_path('Http/Controllers/Admin/AdminGithubController.php'))) {
    Route::get('/setup/deploy-notify', [AdminGithubController::class, 'deployNotify']);
}

Route::redirect('/adminlogin', '/login', 301);
Route::any('/adminlogin/{path}', fn (string $path) => redirect('/'.ltrim($path, '/'), 301))
    ->where('path', '.*');
