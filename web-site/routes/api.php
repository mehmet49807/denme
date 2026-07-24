<?php

use App\Http\Controllers\Web\LocationApiController;
use Illuminate\Support\Facades\Route;

/*
| API routes (prefixed with /api by Laravel).
*/
Route::prefix('v1/locations')->middleware('throttle:120,1,locations-api')->group(function () {
    Route::get('/countries', [LocationApiController::class, 'countries']);
    Route::get('/cities', [LocationApiController::class, 'cities']);
    Route::get('/districts', [LocationApiController::class, 'districts']);
});
