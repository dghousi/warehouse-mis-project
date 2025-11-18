<?php

declare(strict_types=1);

use App\Modules\UserManagement\Infrastructure\Http\Controllers\PermissionController;
use App\Modules\UserManagement\Infrastructure\Http\Controllers\RoleController;
use App\Modules\UserManagement\Infrastructure\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum', 'set_locale'])->prefix('api/v1/user-management')->group(function (): void {
    Route::post('/users/restore/{id}', [UserController::class, 'restore']);
    Route::delete('/users/force/{id}', [UserController::class, 'forceDelete']);
    Route::delete('/users/bulk', [UserController::class, 'bulkDelete']);
    Route::apiResource('users', UserController::class);
    Route::post('/roles/restore/{id}', [RoleController::class, 'restore']);
    Route::delete('/roles/force/{id}', [RoleController::class, 'forceDelete']);
    Route::delete('/roles/bulk', [RoleController::class, 'bulkDelete']);
    Route::apiResource('roles', controller: RoleController::class);
    Route::put('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    Route::post('/permissions/restore/{id}', [PermissionController::class, 'restore']);
    Route::delete('/permissions/force/{id}', [PermissionController::class, 'forceDelete']);
    Route::delete('/permissions/bulk', [PermissionController::class, 'bulkDelete']);
    Route::apiResource('permissions', PermissionController::class);
});
