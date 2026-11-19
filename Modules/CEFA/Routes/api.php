<?php

use Illuminate\Support\Facades\Route;
use Modules\CEFA\Http\Controllers\CEFAController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('cefas', CEFAController::class)->names('cefa');
});
