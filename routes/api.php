<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CurrentUserController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'show']);

    Route::post('/auth/token', [AuthTokenController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', [CurrentUserController::class, 'show']);
    });
});
