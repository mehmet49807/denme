<?php

use Illuminate\Support\Facades\Route;

if (is_file(app_path('Http/Controllers/Admin/AdminAuthController.php'))) {
    Route::middleware('web')->group(base_path('routes/adminlogin.php'));
}

if (class_exists(\App\Http\Controllers\Web\SetupController::class)) {
    Route::get('/setup/cpanel', [\App\Http\Controllers\Web\SetupController::class, 'cpanel']);
    Route::get('/setup/messages', [\App\Http\Controllers\Web\SetupController::class, 'messagesSchema']);
}

Route::redirect('/adminlogin', '/login', 301);
Route::any('/adminlogin/{path}', fn (string $path) => redirect('/'.ltrim($path, '/'), 301))
    ->where('path', '.*');
