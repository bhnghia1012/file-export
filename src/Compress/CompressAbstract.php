<?php

namespace NghiaKun\FileExport\Compress;

abstract class CompressAbstract implements CompressInterface
{
    public function compressFolder(string $source, string $destination, bool $isDeleteSource)
    {

    }

    public function rmdirRecursive($dir)
    {
        foreach (scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (is_dir("$dir/$file")) {
                $this->rmdirRecursive("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }

        rmdir($dir);
    }
}
