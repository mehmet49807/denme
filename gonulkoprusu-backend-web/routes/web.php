<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\MessageAuditorController;
use App\Http\Controllers\Admin\PremiumTrackerController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Web Frontend
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/giris', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/giris', [AuthWebController::class, 'login']);
Route::get('/kayit', [AuthWebController::class, 'showRegister'])->name('register');
Route::post('/kayit', [AuthWebController::class, 'register']);
Route::post('/cikis', [AuthWebController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/akis', [PageController::class, 'feed'])->name('feed');
    Route::get('/profil', [PageController::class, 'profile'])->name('profile');
});

/*
|--------------------------------------------------------------------------
| Admin Panel (right-side menu, cream theme) - admins only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 1. User management
    Route::get('/kullanicilar', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/kullanicilar/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::put('/kullanicilar/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/kullanicilar/{user}/ban', [UserManagementController::class, 'ban'])->name('users.ban');
    Route::delete('/kullanicilar/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // 2. Message auditor
    Route::get('/mesajlar', [MessageAuditorController::class, 'index'])->name('messages.index');

    // 3. Complaints / reports
    Route::get('/sikayetler', [ReportController::class, 'index'])->name('reports.index');
    Route::put('/sikayetler/{report}', [ReportController::class, 'updateStatus'])->name('reports.update');

    // 4. Premium tracker
    Route::get('/premium', [PremiumTrackerController::class, 'index'])->name('premium.index');

    // 5. Admin broadcast system
    Route::get('/duyuru', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::post('/duyuru', [BroadcastController::class, 'send'])->name('broadcast.send');
});
