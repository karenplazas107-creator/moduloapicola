<?php

namespace Modules\Evidencias\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Mapeo de eventos a sus listeners.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];
}
