<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/feed', [FeedController::class, 'index']);
        Route::post('/posts', [FeedController::class, 'store']);
        Route::post('/posts/{post}/like', [FeedController::class, 'like']);
        Route::delete('/posts/{post}/like', [FeedController::class, 'unlike']);

        Route::get('/profile/me', [ProfileController::class, 'me']);
        Route::patch('/profile/me', [ProfileController::class, 'update']);
        Route::get('/profiles/{user}', [ProfileController::class, 'show']);
        Route::post('/profiles/{user}/report', [ProfileController::class, 'report']);
        Route::post('/profiles/{user}/block', [ProfileController::class, 'block']);
        Route::delete('/profiles/{user}/block', [ProfileController::class, 'unblock']);

        Route::get('/stories', [StoryController::class, 'index']);
        Route::post('/stories', [StoryController::class, 'store']);

        Route::get('/premium/packages', [PremiumController::class, 'packages']);
        Route::get('/premium/status', [PremiumController::class, 'status']);
        Route::post('/premium/subscribe', [PremiumController::class, 'subscribe']);

        Route::get('/conversations', [MessageController::class, 'conversations']);
        Route::get('/messages/{user}', [MessageController::class, 'index']);
        Route::post('/messages/{user}', [MessageController::class, 'store']);

        Route::prefix('admin')->middleware('can:admin')->group(function () {
            Route::get('/users', [AdminApiController::class, 'users']);
            Route::patch('/users/{user}', [AdminApiController::class, 'updateUser']);
            Route::post('/users/{user}/ban', [AdminApiController::class, 'banUser']);
            Route::delete('/users/{user}', [AdminApiController::class, 'deleteUser']);
            Route::get('/messages', [AdminApiController::class, 'messages']);
            Route::get('/reports', [AdminApiController::class, 'reports']);
            Route::patch('/reports/{report}', [AdminApiController::class, 'updateReport']);
            Route::get('/premium', [AdminApiController::class, 'premium']);
            Route::post('/broadcasts', [AdminApiController::class, 'broadcast']);
        });
    });
});
