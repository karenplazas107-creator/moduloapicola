<?php

use Illuminate\Support\Facades\Route;
use Modules\Apicola\Http\Controllers\Admin\ApicolaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('apicolas', ApicolaController::class)->names('apicola');
});
