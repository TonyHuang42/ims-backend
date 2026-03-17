<?php

use App\Http\Controllers\Api\V1\Form\FormSubmissionController;
use App\Http\Controllers\Api\V1\Form\FormSubmissionVersionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('form-submissions', FormSubmissionController::class)->except(['destroy']);

    Route::get('form-submissions/{formSubmission}/versions', [FormSubmissionVersionController::class, 'index']);
    Route::get('form-submissions/{formSubmission}/versions/{version}', [FormSubmissionVersionController::class, 'show']);
});
