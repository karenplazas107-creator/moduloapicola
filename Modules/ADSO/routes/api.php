<?php

use Illuminate\Support\Facades\Route;
use Modules\ADSO\Http\Controllers\ADSOController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('adsos', ADSOController::class)->names('adso');
});
