<?php

use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

/*
| Mobile app auth (native login → one-time session handoff).
| Prefixed with /api by Laravel.
*/
Route::post('/mobile/login', [MobileAuthController::class, 'login'])
    ->middleware('throttle:12,1,mobile-login');
