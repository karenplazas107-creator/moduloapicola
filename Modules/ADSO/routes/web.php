<?php

use Illuminate\Support\Facades\Route;
use Modules\ADSO\Http\Controllers\ADSOController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('adsos', ADSOController::class)->names('adso');
});
