<?php

use Illuminate\Support\Facades\Route;
use Modules\RUB\Http\Controllers\RUBController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('rubs', RUBController::class)->names('rub');
});
