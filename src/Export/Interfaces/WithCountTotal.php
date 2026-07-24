<?php

namespace NghiaKun\FileExport\Export\Interfaces;

interface WithCountTotal
{
    /**
     * @return bool
     */
    public function shouldCountTotal(): bool;
}
