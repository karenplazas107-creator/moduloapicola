<?php

use Illuminate\Support\Facades\Route;
use Modules\CEFA\Http\Controllers\CEFAController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cefas', CEFAController::class)->names('cefa');
});
