<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBroadcastController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminPremiumController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

// Admin login (no middleware)
Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('admin.login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// 2FA routes (shared with main site)
Route::get('/two-factor', [\App\Http\Controllers\Web\AuthPageController::class, 'showTwoFactorForm'])->name('2fa.verify');
Route::post('/two-factor', [\App\Http\Controllers\Web\AuthPageController::class, 'verifyTwoFactor'])->middleware('throttle:5,1')->name('2fa.check');
Route::post('/two-factor/resend', [\App\Http\Controllers\Web\AuthPageController::class, 'resendTwoFactor'])->middleware('throttle:3,1')->name('2fa.resend');

// Protected admin routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::post('/users/{user}/ban', [AdminUserController::class, 'ban'])->name('admin.users.ban');
    Route::post('/users/{user}/unban', [AdminUserController::class, 'unban'])->name('admin.users.unban');
    Route::post('/users/{user}/verify-photo', [AdminUserController::class, 'verifyPhoto'])->name('admin.users.verify-photo');
    Route::post('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('admin.users.approve');

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::patch('/reports/{report}', [AdminReportController::class, 'update'])->name('admin.reports.update');

    // Premium
    Route::get('/premium', [AdminPremiumController::class, 'index'])->name('admin.premium.index');
    Route::post('/premium/assign', [AdminPremiumController::class, 'assign'])->name('admin.premium.store');

    // Support
    Route::get('/support', [AdminSupportController::class, 'index'])->name('admin.support.index');
    Route::patch('/support/{ticket}', [AdminSupportController::class, 'update'])->name('admin.support.update');

    // Content
    Route::get('/content/posts', [AdminContentController::class, 'posts'])->name('admin.content.posts');
    Route::delete('/content/posts/{post}', [AdminContentController::class, 'destroyPost'])->name('admin.content.posts.destroy');
    Route::get('/content/stories', [AdminContentController::class, 'stories'])->name('admin.content.stories');
    Route::delete('/content/stories/{story}', [AdminContentController::class, 'destroyStory'])->name('admin.content.stories.destroy');

    // Broadcasts
    Route::get('/broadcasts', [AdminBroadcastController::class, 'index'])->name('admin.broadcasts.index');
    Route::post('/broadcasts', [AdminBroadcastController::class, 'store'])->name('admin.broadcasts.store');

    // Settings
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('admin.settings.index');
    Route::patch('/settings', [AdminSettingController::class, 'update'])->name('admin.settings.update');

    // Messages
    Route::get('/messages', [AdminMessageController::class, 'index'])->name('admin.messages.index');
    Route::get('/messages/{user}', [AdminMessageController::class, 'show'])->name('admin.messages.show');
});
