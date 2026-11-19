<?php

use Illuminate\Support\Facades\Route;
use Modules\RUB\Http\Controllers\RUBController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('rubs', RUBController::class)->names('rub');
});
