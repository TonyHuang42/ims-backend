<?php

use App\Http\Controllers\Api\V1\Identity\DepartmentController;
use App\Http\Controllers\Api\V1\Identity\PermissionController;
use App\Http\Controllers\Api\V1\Identity\RoleController;
use App\Http\Controllers\Api\V1\Identity\TeamController;
use App\Http\Controllers\Api\V1\Identity\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('users', UserController::class)->except(['destroy']);
    Route::post('users/{user}/roles', [UserController::class, 'syncRoles']);
    Route::get('users/{user}/roles', [UserController::class, 'getRoles']);
    Route::post('users/{user}/departments', [UserController::class, 'syncDepartments']);
    Route::get('users/{user}/departments', [UserController::class, 'getDepartments']);
    Route::post('users/{user}/teams', [UserController::class, 'syncTeams']);
    Route::get('users/{user}/teams', [UserController::class, 'getTeams']);

    Route::apiResource('departments', DepartmentController::class)->except(['destroy']);
    Route::apiResource('teams', TeamController::class)->except(['destroy']);

    Route::apiResource('roles', RoleController::class)->except(['destroy']);
    Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
    Route::get('roles/{role}/permissions', [RoleController::class, 'getPermissions']);

    Route::apiResource('permissions', PermissionController::class)->except(['destroy']);
});
