<?php

namespace NghiaKun\FileExport\Export;

use NghiaKun\FileExport\Export\Interfaces\FromQuery;
use NghiaKun\FileExport\Export\Interfaces\PrimaryKey;
use NghiaKun\FileExport\Export\Interfaces\WithChunkReading;
use NghiaKun\FileExport\Export\Interfaces\WithCountTotal;
use NghiaKun\FileExport\Export\Interfaces\WithCustomCsvSettings;
use NghiaKun\FileExport\Export\Interfaces\WithHeadings;
use NghiaKun\FileExport\Export\Interfaces\WithMapping;
use NghiaKun\FileExport\Export\Interfaces\WithReadingStrategy;

abstract class BaseExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithChunkReading,
    WithCountTotal,
    WithCustomCsvSettings,
    WithReadingStrategy,
    PrimaryKey
{
    public function chunkSize(): int
    {
        return config('export.chunk_size', 1000);
    }

    public function primaryKey(): string
    {
        return config('export.primary_key', 'id');
    }

    public function readingStrategy(): string
    {
        return config('export.reading_strategy', 'cursor');
    }

    public function shouldCountTotal(): bool
    {
        return config('export.count_total', true);
    }

    public function getCsvSettings(): array
    {
        return config('export.csv', [
            'delimiter' => ',',
            'enclosure' => '',
        ]);
    }
}
