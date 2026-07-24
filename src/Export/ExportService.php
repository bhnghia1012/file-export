<?php

namespace NghiaKun\FileExport\Export;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use NghiaKun\FileExport\Compress\ZipService;

class ExportService
{
    public const STRATEGY_CURSOR = 'cursor';

    public const STRATEGY_CHUNK_BY_ID = 'chunkById';

    protected BaseExport $exportModel;
    protected string $location;
    protected string $format;
    protected bool $countTotal;
    protected int $chunkSize;
    protected array $headings;
    protected $myFile;
    protected string $primaryKey;
    protected string $readingStrategy;
    protected array $settings;

    protected int $total = 0;
    protected int $recordsPerFile;
    protected int $currentFileRecords = 0;
    protected int $currentFileIndex = 0;
    protected array $tsvFiles = [];
    protected string $exportFolder = '';


    public function __construct(BaseExport $exportModel, string $location, string $format)
    {
        $this->exportModel = $exportModel;
        $this->location = $location;
        $this->format = $format;

        $this->chunkSize = $exportModel->chunkSize();
        $this->headings = $exportModel->headings();
        $this->primaryKey = $exportModel->primaryKey();
        $this->readingStrategy = $exportModel->readingStrategy();
        $this->countTotal = $exportModel->shouldCountTotal();
        $this->settings = $exportModel->getCsvSettings();
        $this->recordsPerFile = config('export.records_per_file', 50000);
    }

    public function handle()
    {
        if ($this->format === 'zip') {
            $this->handleZipExport();
        } else {
            $this->handleTsvExport();
        }
    }

    protected function handleZipExport()
    {
        $pathInfo = pathinfo($this->location);
        $baseName = $pathInfo['filename'];
        $directory = $pathInfo['dirname'];
        $this->exportFolder = $directory . DIRECTORY_SEPARATOR . $baseName;

        if (! is_dir($this->exportFolder)) {
            mkdir($this->exportFolder, 0755, true);
        }

        try {
            $this->updateTotal();
            $this->openNewTsvFile();

            $this->exportFromQueryForZip();

            if ($this->myFile) {
                fclose($this->myFile);
                $this->myFile = null;
            }

            $zipPath = $this->location;
            $zip = new ZipService();
            $zip->compressFolder($this->exportFolder, $zipPath, true);
        } catch (\Throwable $e) {
            if ($this->myFile) {
                fclose($this->myFile);
                $this->myFile = null;
            }

            File::deleteDirectory($this->exportFolder);

            throw $e;
        }
    }

    protected function handleTsvExport()
    {
        $this->myFile = fopen($this->location . '.tmp', 'w');
        chmod($this->location . '.tmp', 0644);

        try {
            if (! empty($this->headings)) {
                $header = implode($this->settings['delimiter'], $this->headings) . "\n";
                fwrite($this->myFile, $header);
            }

            if ($this->countTotal) {
                $this->total = $this->exportModel->query()->count();
                Cache::put(md5(basename($this->location) . '_total'), $this->total, 60 * 60);
            }

            $this->exportFromQuery();

            fclose($this->myFile);
            $this->myFile = null;
            rename($this->location . '.tmp', $this->location);
        } catch (\Throwable $e) {
            if ($this->myFile) {
                fclose($this->myFile);
                $this->myFile = null;
            }

            if (file_exists($this->location . '.tmp')) {
                unlink($this->location . '.tmp');
            }

            throw $e;
        }
    }

    protected function exportFromQuery()
    {
        if ($this->readingStrategy === self::STRATEGY_CHUNK_BY_ID) {
            $this->exportModel->query()->chunkById($this->chunkSize, function (Collection|SupportCollection $collection) {
                foreach ($collection as $item) {
                    fwrite($this->myFile, $this->getOutput($item));
                }
            }, $this->primaryKey);

            return;
        }

        foreach ($this->exportModel->query()->cursor() as $item) {
            fwrite($this->myFile, $this->getOutput($item));
        }
    }

    protected function getOutput($item): string
    {
        $fields = $this->exportModel->map($item);

        $fields = array_map(fn ($field) => $this->sanitizeField($field), $fields);

        return implode($this->settings['delimiter'], $fields) . "\n";
    }

    protected function sanitizeField($value): string
    {
        return str_replace(["\r\n", "\n", "\r", $this->settings['delimiter']], ' ', (string) $value);
    }

    protected function exportFromQueryForZip(): void
    {
        if ($this->readingStrategy === self::STRATEGY_CHUNK_BY_ID) {
            $this->exportModel->query()->chunkById($this->chunkSize, function (Collection|SupportCollection $collection) {
                foreach ($collection as $item) {
                    if ($this->currentFileRecords >= $this->recordsPerFile) {
                        $this->openNewTsvFile();
                    }

                    fwrite($this->myFile, $this->getOutput($item));
                    $this->currentFileRecords++;
                }
            }, $this->primaryKey);

            return;
        }

        foreach ($this->exportModel->query()->cursor() as $item) {
            if ($this->currentFileRecords >= $this->recordsPerFile) {
                $this->openNewTsvFile();
            }

            fwrite($this->myFile, $this->getOutput($item));
            $this->currentFileRecords++;
        }
    }

    protected function openNewTsvFile(): string
    {
        if ($this->myFile) {
            fclose($this->myFile);
        }

        $pathInfo = pathinfo($this->location);
        $baseName = $pathInfo['filename'];

        $this->currentFileIndex++;
        $fileName = sprintf('%s_part_%d.tsv', $baseName, $this->currentFileIndex);
        $filePath = $this->exportFolder . DIRECTORY_SEPARATOR . $fileName;

        $this->tsvFiles[] = $filePath;

        $this->myFile = fopen($filePath, 'w');
        chmod($filePath, 0644);
        $this->currentFileRecords = 0;

        if (! empty($this->headings)) {
            $header = implode($this->settings['delimiter'], $this->headings) . "\n";
            fwrite($this->myFile, $header);
        }

        return $filePath;
    }

    private function updateTotal()
    {
        if ($this->countTotal) {
            $this->total = $this->exportModel->query()->count();
            Cache::put(md5(basename($this->location) . '_total'), $this->total, 60 * 60);
        }
    }
}
