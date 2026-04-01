<?php

namespace MrCatz\DataTable;

use Illuminate\Support\ServiceProvider;
use MrCatz\DataTable\Console\MrcatzMakeCommand;
use MrCatz\DataTable\Console\MrcatzRemoveCommand;

class MrCatzServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mrcatz');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mrcatz'),
        ], 'mrcatz-views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MrcatzMakeCommand::class,
                MrcatzRemoveCommand::class,
            ]);
        }
    }
}
