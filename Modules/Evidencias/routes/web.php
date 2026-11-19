<?php

use Illuminate\Support\Facades\Route;
use Modules\Evidencias\Http\Controllers\EvidenciaController;

/*
|--------------------------------------------------------------------------
| Rutas del Panel de Evidencias
|--------------------------------------------------------------------------
| Acceso exclusivo al usuario con ID 124 (gestor de evidencias).
| El middleware 'gestor.evidencias' verifica autenticación + ID autorizado.
| Prefijo: /evidencias — no colisiona con /admin ni con otros módulos.
|--------------------------------------------------------------------------
*/

Route::prefix('evidencias')
    ->name('evidencias.')
    ->middleware(['web', 'gestor.evidencias'])
    ->group(function () {

        // Dashboard principal (lista + resumen)
        Route::get('/', [EvidenciaController::class, 'index'])->name('index');

        // CRUD de evidencias
        Route::get('/crear',            [EvidenciaController::class, 'create'])->name('create');
        Route::post('/',                [EvidenciaController::class, 'store'])->name('store');
        Route::get('/{id}',             [EvidenciaController::class, 'show'])->name('show');
        Route::get('/{id}/editar',      [EvidenciaController::class, 'edit'])->name('edit');
        Route::put('/{id}',             [EvidenciaController::class, 'update'])->name('update');
        Route::delete('/{id}',          [EvidenciaController::class, 'destroy'])->name('destroy');
    });
