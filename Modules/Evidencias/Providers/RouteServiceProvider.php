<?php

namespace Modules\Evidencias\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Evidencias';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
    }

    /**
     * Registra las rutas web del módulo con el middleware 'web'.
     * Las rutas específicas de autorización se aplican dentro del archivo de rutas.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path($this->name, '/routes/web.php'));
    }
}
