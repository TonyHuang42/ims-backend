<?php

use App\Http\Controllers\Api\V1\Identity\DepartmentController;
use App\Http\Controllers\Api\V1\Identity\RoleController;
use App\Http\Controllers\Api\V1\Identity\TeamController;
use App\Http\Controllers\Api\V1\Identity\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/roles', [UserController::class, 'syncRoles']);
    Route::get('users/{user}/roles', [UserController::class, 'getRoles']);

    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('teams', TeamController::class);
    Route::apiResource('roles', RoleController::class);
});
