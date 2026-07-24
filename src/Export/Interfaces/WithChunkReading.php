<?php

namespace NghiaKun\FileExport\Export\Interfaces;

interface WithChunkReading
{
    /**
     * @return int
     */
    public function chunkSize(): int;
}
