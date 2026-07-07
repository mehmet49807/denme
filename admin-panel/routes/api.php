<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Admin\AdminBroadcastController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminPremiumController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth (public)
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:12,1');

    // Locations (public)
    Route::get('/locations/countries', [LocationController::class, 'countries']);
    Route::get('/locations/cities', [LocationController::class, 'cities']);
    Route::get('/locations/districts', [LocationController::class, 'districts']);

    // Premium packages (public)
    Route::get('/premium/packages', [PremiumController::class, 'packages']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Profile
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

        // Users (public view)
        Route::get('/users/{username}', [UserController::class, 'show']);

        // Feed & Posts
        Route::get('/feed', [FeedController::class, 'index']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::delete('/posts/{id}', [PostController::class, 'destroy']);
        Route::post('/posts/{id}/like', [PostController::class, 'toggleLike']);

        // Stories
        Route::get('/stories', [StoryController::class, 'index']);
        Route::post('/stories', [StoryController::class, 'store']);
        Route::delete('/stories/{id}', [StoryController::class, 'destroy']);

        // Safety
        Route::post('/users/{id}/report', [ReportController::class, 'store']);
        Route::post('/users/{id}/block', [BlockController::class, 'store']);
        Route::delete('/users/{id}/block', [BlockController::class, 'destroy']);
        Route::get('/blocks', [BlockController::class, 'index']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/badge-counts', [NotificationController::class, 'badgeCounts']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

        // Messaging
        Route::get('/conversations', [MessageController::class, 'conversations']);
        Route::get('/conversations/{userId}/messages', [MessageController::class, 'messages']);
        Route::post('/conversations/{userId}/messages', [MessageController::class, 'send']);

        // Premium
        Route::get('/premium/status', [PremiumController::class, 'status']);
        Route::post('/premium/subscribe', [PremiumController::class, 'subscribe']);

        // Admin routes
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index']);

            Route::get('/users', [AdminUserController::class, 'index']);
            Route::get('/users/{id}', [AdminUserController::class, 'show']);
            Route::put('/users/{id}', [AdminUserController::class, 'update']);
            Route::post('/users/{id}/ban', [AdminUserController::class, 'ban']);
            Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

            Route::get('/messages', [AdminMessageController::class, 'index']);
            Route::get('/messages/{id}', [AdminMessageController::class, 'show']);

            Route::get('/reports', [AdminReportController::class, 'index']);
            Route::put('/reports/{id}', [AdminReportController::class, 'update']);

            Route::get('/premium', [AdminPremiumController::class, 'index']);

            Route::get('/broadcasts', [AdminBroadcastController::class, 'index']);
            Route::post('/broadcasts', [AdminBroadcastController::class, 'store']);
        });
    });
});
