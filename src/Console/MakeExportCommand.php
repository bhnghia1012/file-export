<?php

namespace NghiaKun\FileExport\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeExportCommand extends GeneratorCommand
{
    protected $name = 'make:export';

    protected $description = 'Create a new export class';

    protected $type = 'Export';

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/export.stub');
    }

    protected function resolveStubPath($stub)
    {
        $customPath = $this->laravel->basePath(trim($stub, '/'));

        return file_exists($customPath) ? $customPath : __DIR__ . '/../..' . $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Exports';
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the export already exists'],
        ];
    }
}
