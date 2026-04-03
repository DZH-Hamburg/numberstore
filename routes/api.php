<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CurrentUserController;
use App\Http\Controllers\Api\V1\ElementController;
use App\Http\Controllers\Api\V1\ElementScreenshotController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'show']);

    Route::post('/auth/token', [AuthTokenController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', [CurrentUserController::class, 'show']);

        Route::get('/groups', [GroupController::class, 'index']);
        Route::post('/groups', [GroupController::class, 'store']);
        Route::get('/groups/{group}', [GroupController::class, 'show']);
        Route::delete('/groups/{group}', [GroupController::class, 'destroy']);

        Route::get('/groups/{group}/elements', [ElementController::class, 'index']);
        Route::post('/groups/{group}/elements', [ElementController::class, 'store']);
        Route::get('/groups/{group}/elements/{element}', [ElementController::class, 'show']);
        Route::patch('/groups/{group}/elements/{element}', [ElementController::class, 'update']);
        Route::delete('/groups/{group}/elements/{element}', [ElementController::class, 'destroy']);

        Route::get('/groups/{group}/elements/{element}/screenshot', [ElementScreenshotController::class, 'show']);
        Route::post('/groups/{group}/elements/{element}/screenshot', [ElementScreenshotController::class, 'store']);
    });
});
