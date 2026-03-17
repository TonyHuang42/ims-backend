<?php

use App\Http\Controllers\Api\V1\Form\FormTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('form-templates', FormTemplateController::class)->except(['destroy']);
});
