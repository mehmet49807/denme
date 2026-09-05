<?php

use App\Http\Controllers\Web\AuthPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'locale'])->group(function () {
    Route::post('/email/verification-code', [AuthPageController::class, 'verifyEmailCode'])
        ->middleware('throttle:8,1,email-verification-code')
        ->name('verification.code');
});
