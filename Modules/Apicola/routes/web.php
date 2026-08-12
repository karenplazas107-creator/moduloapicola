<?php

use Illuminate\Support\Facades\Route;
use Modules\Apicola\Http\Controllers\ApicolaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('apicolas', ApicolaController::class)->names('apicola');
});
