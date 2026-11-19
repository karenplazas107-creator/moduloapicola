<?php

use Illuminate\Support\Facades\Route;
use Modules\SISGEDI\Http\Controllers\SISGEDIController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('sisgedis', SISGEDIController::class)->names('sisgedi');
});
