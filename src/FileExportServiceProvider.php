<?php

namespace NghiaKun\FileExport;

use Illuminate\Support\ServiceProvider;
use NghiaKun\FileExport\Compress\CompressInterface;
use NghiaKun\FileExport\Compress\ZipService;
use NghiaKun\FileExport\Console\MakeExportCommand;

class FileExportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/export.php', 'export');

        $this->app->bind(CompressInterface::class, ZipService::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeExportCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../stubs/export.stub' => base_path('stubs/export.stub'),
            ], 'file-export-stubs');

            $this->publishes([
                __DIR__ . '/../config/export.php' => config_path('export.php'),
            ], 'file-export-config');
        }
    }
}
