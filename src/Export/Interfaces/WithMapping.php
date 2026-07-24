<?php

namespace NghiaKun\FileExport\Export\Interfaces;

interface WithMapping
{
    /**
     * @param  mixed  $row
     *
     * @return array
     */
    public function map($row): array;
}
