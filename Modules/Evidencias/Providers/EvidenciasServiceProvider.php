<?php

namespace Modules\Evidencias\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class EvidenciasServiceProvider extends ModuleServiceProvider
{
    /**
     * Nombre del módulo.
     */
    protected string $name = 'Evidencias';

    /**
     * Nombre en minúsculas del módulo.
     */
    protected string $nameLower = 'evidencias';

    /**
     * Providers adicionales del módulo.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
