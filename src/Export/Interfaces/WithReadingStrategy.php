<?php

namespace NghiaKun\FileExport\Export\Interfaces;

interface WithReadingStrategy
{
    /**
     * @return string
     */
    public function readingStrategy(): string;
}
