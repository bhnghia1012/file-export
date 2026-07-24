<?php

namespace NghiaKun\FileExport;

use Illuminate\Support\ServiceProvider;
use NghiaKun\FileExport\Compress\CompressInterface;
use NghiaKun\FileExport\Compress\ZipService;

class FileExportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CompressInterface::class, ZipService::class);
    }
}
