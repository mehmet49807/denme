<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SafetyController;
use App\Http\Controllers\Api\StoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API (v1) - shared by Web, Android and iOS clients
|--------------------------------------------------------------------------
| Auth is token-based (Laravel Sanctum) so the same credentials work on
| every platform with real-time state synced through the central DB.
*/

Route::prefix('v1')->group(function () {

    // ---- Public ----
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/premium/packages', [PremiumController::class, 'packages']);

    // ---- Authenticated ----
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'showSelf']);
        Route::put('/profile', [ProfileController::class, 'update']); // username is read-only
        Route::get('/users/{user}', [ProfileController::class, 'show']);

        // Feed (straight matching, comments disabled)
        Route::get('/feed', [FeedController::class, 'index']);
        Route::post('/posts', [FeedController::class, 'store']);
        Route::post('/posts/{post}/like', [FeedController::class, 'like']);
        Route::delete('/posts/{post}', [FeedController::class, 'destroy']);

        // Stories (premium men only)
        Route::get('/stories', [StoryController::class, 'index']);
        Route::post('/stories', [StoryController::class, 'store']);

        // Premium (men only)
        Route::get('/premium/status', [PremiumController::class, 'status']);
        Route::post('/premium/subscribe', [PremiumController::class, 'subscribe']);

        // Messaging
        Route::get('/conversations', [MessageController::class, 'conversations']);
        Route::get('/conversations/{user}', [MessageController::class, 'thread']);
        Route::post('/conversations/{user}', [MessageController::class, 'send']);

        // Safety - available on every profile
        Route::post('/users/{user}/report', [SafetyController::class, 'report']);
        Route::post('/users/{user}/block', [SafetyController::class, 'block']);
        Route::delete('/users/{user}/block', [SafetyController::class, 'unblock']);
    });
});
