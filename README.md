# nghia-kun/laravel-file-export

Export dữ liệu Eloquent/Query Builder ra file TSV hoặc file ZIP (gồm nhiều file TSV theo batch), dùng chung cho nhiều project Laravel.

## Cài đặt

## Sử dụng

### 1. Tạo class export, extends `BaseExport`

```php
use NghiaKun\FileExport\Export\BaseExport;
use NghiaKun\FileExport\Export\Interfaces\WithHeadings;
use NghiaKun\FileExport\Export\Interfaces\WithMapping;

class UserExport extends BaseExport implements WithHeadings, WithMapping
{
    public function query()
    {
        return User::query();
    }

    public function headings(): array
    {
        return ['id', 'name', 'email'];
    }

    public function map($user): array
    {
        return [$user->id, $user->name, $user->email];
    }
}
```

Các interface tùy chọn khác: `WithChunkReading`, `WithCountTotal`, `WithCustomCsvSettings`, `WithReadingStrategy`, `PrimaryKey`.

### 2. Gọi `ExportService`

```php
use NghiaKun\FileExport\Export\ExportService;

// Xuất ra 1 file .tsv
(new ExportService(new UserExport(), storage_path('app/users.tsv'), 'tsv'))->handle();

// Xuất ra 1 file .zip chứa nhiều file .tsv (chia theo recordsPerFile)
(new ExportService(new UserExport(), storage_path('app/users.zip'), 'zip'))->handle();
```

### 3. Dùng riêng `ZipService` để nén 1 thư mục

```php
use NghiaKun\FileExport\Compress\ZipService;

(new ZipService())->compressFolder($sourceDir, $zipDestination, true);
```
