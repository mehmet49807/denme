<?php

use App\Http\Controllers\Web\AuthPageController;
use App\Http\Controllers\Web\ProfilePageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'locale'])->group(function () {
    Route::post('/email/verification-code', [AuthPageController::class, 'verifyEmailCode'])
        ->middleware('throttle:8,1,email-verification-code')
        ->name('verification.code');

    Route::post('/profile/notification-prefs', [ProfilePageController::class, 'updateNotificationPrefs'])
        ->middleware('throttle:20,1,notification-prefs')
        ->name('profile.notification-prefs');
});
