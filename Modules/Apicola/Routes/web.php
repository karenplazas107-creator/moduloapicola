<?php

use Illuminate\Support\Facades\Route;
use Modules\Apicola\Http\Controllers\Admin\ApicolaController;
use Modules\Apicola\Http\Controllers\Admin\ApiarioController;
use Modules\Apicola\Http\Controllers\Admin\ColmenaController;
use Modules\Apicola\Http\Controllers\Admin\InspeccionController;
use Modules\Apicola\Http\Controllers\Admin\CalendarioController;
use Modules\Apicola\Http\Controllers\Admin\InventarioController;
use Modules\Apicola\Http\Controllers\Admin\UserController;
use Modules\Apicola\Http\Controllers\Admin\ReporteController;
use Modules\Apicola\Http\Controllers\Aprendiz\AprendizController;
use Modules\Apicola\Http\Controllers\Aprendiz\ColmenaController as AprendizColmenaController;
use Modules\Apicola\Http\Controllers\Aprendiz\InspeccionController as AprendizInspeccionController;

// Soporte para URL con mayúscula o minúscula según registro en SICA (/Apicola o /apicola)
Route::get('Apicola', function () {
    return redirect()->route('apicola.welcome');
});

// Ruta pública - Landing page con redirección directa al panel para usuarios autenticados
Route::get('apicola', function () {
    if (auth()->check() && !request()->has('public')) {
        $user = auth()->user();
        if ($user->roles->contains('slug', 'apicola.aprendiz') && !$user->roles->contains('slug', 'apicola.admin') && !$user->hasSuperAdmin()) {
            return redirect()->route('apicola.aprendiz.dashboard');
        }
        if ($user->roles->contains('slug', 'apicola.admin') || $user->hasSuperAdmin()) {
            return redirect()->route('apicola.admin.dashboard');
        }
    }
    return view('apicola::welcome');
})->name('apicola.welcome');

// Ruta de redirección directa tras autenticación
Route::get('apicola/auth-redirect', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->roles->contains('slug', 'apicola.aprendiz') && !$user->roles->contains('slug', 'apicola.admin') && !$user->hasSuperAdmin()) {
            return redirect()->route('apicola.aprendiz.dashboard');
        }
        if ($user->roles->contains('slug', 'apicola.admin') || $user->hasSuperAdmin()) {
            return redirect()->route('apicola.admin.dashboard');
        }
    }
    return redirect()->route('apicola.welcome');
})->name('apicola.auth.redirect');

// Rutas protegidas - Panel de administración exclusivo para Administrador Apícola (y Superadmin)
Route::prefix('apicola')->middleware(['auth', 'apicola.admin'])->group(function () {
    Route::get('admin', [ApicolaController::class, 'dashboard'])->name('apicola.admin.dashboard');
    Route::get('dashboard', [ApicolaController::class, 'dashboard'])->name('apicola.dashboard');
    
    // Módulo de Apiarios
    Route::get('apiarios', [ApiarioController::class, 'index'])->name('apicola.admin.apiarios.index');
    Route::post('apiarios', [ApiarioController::class, 'store'])->name('apicola.admin.apiarios.store');
    Route::match(['put', 'post'], 'apiarios/{id}', [ApiarioController::class, 'update'])->name('apicola.admin.apiarios.update');
    Route::delete('apiarios/{id}', [ApiarioController::class, 'destroy'])->name('apicola.admin.apiarios.destroy');
    Route::post('apiarios/{id}/toggle-status', [ApiarioController::class, 'toggleStatus'])->name('apicola.admin.apiarios.toggle-status');

    // Módulo de Colmenas
    Route::post('colmenas/{id}/toggle-status', [ColmenaController::class, 'toggleStatus'])->name('apicola.admin.colmenas.toggle-status');
    Route::resource('colmenas', ColmenaController::class)->names('apicola.admin.colmenas');

    // Submódulo de Producción e Inspección
    Route::get('inspecciones', [InspeccionController::class, 'index'])->name('apicola.admin.inspecciones.index');
    Route::get('colmenas/{code}/inspecciones', [InspeccionController::class, 'byColmena'])->name('apicola.admin.colmenas.inspecciones');
    Route::post('inspecciones', [InspeccionController::class, 'store'])->name('apicola.admin.inspecciones.store');
    Route::delete('inspecciones/{id}', [InspeccionController::class, 'destroy'])->name('apicola.admin.inspecciones.destroy');
    Route::post('inspecciones/{id}/toggle-status', [InspeccionController::class, 'toggleStatus'])->name('apicola.admin.inspecciones.toggle-status');

    // Módulo de Calendario Floral
    Route::get('calendario', [CalendarioController::class, 'index'])->name('apicola.admin.calendario.index');
    Route::post('calendario', [CalendarioController::class, 'store'])->name('apicola.admin.calendario.store');
    Route::put('calendario/{id}', [CalendarioController::class, 'update'])->name('apicola.admin.calendario.update');
    Route::delete('calendario/{id}', [CalendarioController::class, 'destroy'])->name('apicola.admin.calendario.destroy');
    Route::post('calendario/{id}/toggle-status', [CalendarioController::class, 'toggleStatus'])->name('apicola.admin.calendario.toggle-status');

    // Módulo de Inventario
    Route::get('inventario', [InventarioController::class, 'index'])->name('apicola.admin.inventario.index');
    Route::get('movimientos', [InventarioController::class, 'movimientosIndex'])->name('apicola.admin.movimientos.index');
    Route::get('inventario/movimientos', [InventarioController::class, 'movimientosIndex'])->name('apicola.admin.inventario.movimientos.index');
    Route::post('inventario/movimientos', [InventarioController::class, 'movimientosStore'])->name('apicola.admin.inventario.movimientos.store');
    Route::get('inventario/movimientos/{id}', [InventarioController::class, 'movimientosShow'])->name('apicola.admin.inventario.movimientos.show');
    Route::get('inventario/movimientos/{id}/imprimir', [InventarioController::class, 'movimientosPrint'])->name('apicola.admin.inventario.movimientos.print');
    Route::post('inventario', [InventarioController::class, 'store'])->name('apicola.admin.inventario.store');
    Route::get('inventario/{id}', [InventarioController::class, 'show'])->name('apicola.admin.inventario.show');
    Route::put('inventario/{id}', [InventarioController::class, 'update'])->name('apicola.admin.inventario.update');
    Route::delete('inventario/{id}', [InventarioController::class, 'destroy'])->name('apicola.admin.inventario.destroy');
    Route::post('inventario/{id}/toggle-status', [InventarioController::class, 'toggleStatus'])->name('apicola.admin.inventario.toggle-status');
    Route::post('inventario/{id}/movimiento', [InventarioController::class, 'registrarMovimiento'])->name('apicola.admin.inventario.movimiento');

    // Módulo de Gestión de Usuarios
    Route::get('usuarios', [UserController::class, 'index'])->name('apicola.admin.usuarios.index');
    Route::post('usuarios', [UserController::class, 'store'])->name('apicola.admin.usuarios.store');
    Route::get('usuarios/{id}', [UserController::class, 'show'])->name('apicola.admin.usuarios.show');
    Route::match(['put', 'post'], 'usuarios/{id}', [UserController::class, 'update'])->name('apicola.admin.usuarios.update');
    Route::post('usuarios/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('apicola.admin.usuarios.toggle-status');
    Route::post('usuarios/buscar-persona', [UserController::class, 'searchPerson'])->name('apicola.admin.usuarios.search-person');

    // Módulo de Reportes
    Route::get('reportes', [ReporteController::class, 'index'])->name('apicola.admin.reportes.index');
    Route::get('reportes/preview', [ReporteController::class, 'previewData'])->name('apicola.admin.reportes.preview');
    Route::get('reportes/excel', [ReporteController::class, 'exportExcel'])->name('apicola.admin.reportes.excel');
    Route::get('reportes/pdf', [ReporteController::class, 'exportPdf'])->name('apicola.admin.reportes.pdf');
});

Route::middleware(['auth', 'apicola.admin'])->group(function () {
    Route::resource('apicolas', ApicolaController::class)->names('apicola');
});

// Rutas exclusivas para el rol de Aprendiz Apícola (Módulo único: Colmenas)
Route::prefix('apicola/aprendiz')->middleware(['auth', 'apicola.aprendiz'])->group(function () {
    Route::get('/', [AprendizColmenaController::class, 'index'])->name('apicola.aprendiz.dashboard');
    Route::get('dashboard', [AprendizColmenaController::class, 'index'])->name('apicola.aprendiz.dashboard.alias');
    Route::get('colmenas', [AprendizColmenaController::class, 'index'])->name('apicola.aprendiz.colmenas.index');
    Route::post('colmenas', [AprendizColmenaController::class, 'store'])->name('apicola.aprendiz.colmenas.store');
    Route::put('colmenas/{id}', [AprendizColmenaController::class, 'update'])->name('apicola.aprendiz.colmenas.update');
    Route::post('colmenas/{id}/toggle-status', [AprendizColmenaController::class, 'toggleStatus'])->name('apicola.aprendiz.colmenas.toggle-status');
    Route::delete('colmenas/{id}', [AprendizColmenaController::class, 'destroy'])->name('apicola.aprendiz.colmenas.destroy');

    // Submódulo de Producción e Inspección
    Route::get('inspecciones', [AprendizInspeccionController::class, 'index'])->name('apicola.aprendiz.inspecciones.index');
    Route::get('colmenas/{code}/inspecciones', [AprendizInspeccionController::class, 'byColmena'])->name('apicola.aprendiz.colmenas.inspecciones');
    Route::post('inspecciones', [AprendizInspeccionController::class, 'store'])->name('apicola.aprendiz.inspecciones.store');
    Route::delete('inspecciones/{id}', [AprendizInspeccionController::class, 'destroy'])->name('apicola.aprendiz.inspecciones.destroy');
    Route::post('inspecciones/{id}/toggle-status', [AprendizInspeccionController::class, 'toggleStatus'])->name('apicola.aprendiz.inspecciones.toggle-status');
});
