<?php

namespace MrCatz\DataTable;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MrCatz\DataTable\Console\MrcatzCleanupEditorImagesCommand;
use MrCatz\DataTable\Console\MrcatzMakeCommand;
use MrCatz\DataTable\Console\MrcatzMakeSearchProxyCommand;
use MrCatz\DataTable\Console\MrcatzMeilisearchConfigureCommand;
use MrCatz\DataTable\Console\MrcatzRemoveCommand;
use MrCatz\DataTable\Http\EditorImageUploadController;

class MrCatzServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mrcatz.php', 'mrcatz');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mrcatz');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'mrcatz');

        $this->publishes([
            __DIR__ . '/../config/mrcatz.php' => config_path('mrcatz.php'),
        ], 'mrcatz-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mrcatz'),
        ], 'mrcatz-views');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/mrcatz'),
        ], 'mrcatz-lang');

        $this->publishes([
            __DIR__ . '/../resources/views/exports/datatable-excel.blade.php' => resource_path('views/exports/datatable-excel.blade.php'),
            __DIR__ . '/../resources/views/exports/datatable-pdf.blade.php' => resource_path('views/exports/datatable-pdf.blade.php'),
            __DIR__ . '/../stubs/DatatableExport.php' => app_path('Exports/DatatableExport.php'),
        ], 'mrcatz-export');

        $this->registerEditorUploadRoute();

        if ($this->app->runningInConsole()) {
            $this->commands([
                MrcatzCleanupEditorImagesCommand::class,
                MrcatzMakeCommand::class,
                MrcatzMakeSearchProxyCommand::class,
                MrcatzMeilisearchConfigureCommand::class,
                MrcatzRemoveCommand::class,
            ]);
        }
    }

    protected function registerEditorUploadRoute(): void
    {
        if (config('mrcatz.editor_image.mode') !== 'upload') {
            return;
        }

        Route::middleware(['web', 'auth'])
            ->post('/mrcatz/editor/upload-image', EditorImageUploadController::class)
            ->name('mrcatz.editor.upload-image');
    }
}
