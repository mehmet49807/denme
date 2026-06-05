<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'web.home')->name('home');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/messages', [AdminDashboardController::class, 'messages'])->name('messages');
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/premium', [AdminDashboardController::class, 'premium'])->name('premium');
    Route::get('/broadcasts', [AdminDashboardController::class, 'broadcasts'])->name('broadcasts');
});
