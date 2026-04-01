<?php

namespace MrCatz\DataTable;

use Illuminate\Support\ServiceProvider;

class MrCatzServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mrcatz');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mrcatz'),
        ], 'mrcatz-views');
    }
}
