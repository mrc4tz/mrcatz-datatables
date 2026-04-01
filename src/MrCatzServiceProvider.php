<?php

namespace MrCatz\DataTable;

use Illuminate\Support\ServiceProvider;
use MrCatz\DataTable\Console\MrcatzMakeCommand;
use MrCatz\DataTable\Console\MrcatzRemoveCommand;

class MrCatzServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mrcatz.php', 'mrcatz');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mrcatz');

        $this->publishes([
            __DIR__ . '/../config/mrcatz.php' => config_path('mrcatz.php'),
        ], 'mrcatz-config');

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
