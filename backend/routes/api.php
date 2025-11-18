<?php

declare(strict_types=1);

use App\Modules\Auth\Infrastructure\Http\Controllers\AuthController;
use App\Modules\UserManagement\Infrastructure\Http\Controllers\UserController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::middleware('web')->prefix('auth')->group(function (): void {
    Route::post(uri: '/login', action: [AuthenticatedSessionController::class, 'store']);

    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/user/locale', [UserController::class, 'setLocale']);
});
