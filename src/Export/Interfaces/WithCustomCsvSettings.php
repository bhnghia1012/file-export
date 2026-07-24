<?php

namespace NghiaKun\FileExport\Export\Interfaces;

interface WithCustomCsvSettings
{
    /**
     * @return array
     */
    public function getCsvSettings(): array;
}
