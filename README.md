# nghia-kun/laravel-file-export

Export dữ liệu Eloquent/Query Builder ra file TSV hoặc file ZIP (gồm nhiều file TSV theo batch), dùng chung cho nhiều project Laravel.

## Cài đặt

## Sử dụng

### 1. Tạo class export bằng command

```bash
php artisan make:export UserExport
```

Mặc định sinh file tại `app/Exports/UserExport.php`, hoặc dùng subpath: `php artisan make:export Reports/UserExport` → `app/Exports/Reports/UserExport.php`.

Muốn tùy chỉnh stub, publish ra rồi sửa:

```bash
php artisan vendor:publish --tag=file-export-stubs
```

(sẽ tạo `stubs/export.stub` trong project, command sẽ tự ưu tiên dùng file này nếu tồn tại)

Hoặc tự tạo class export thủ công, extends `BaseExport`:

```php
use NghiaKun\FileExport\Export\BaseExport;

class UserExport extends BaseExport
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

`BaseExport` đã implement sẵn toàn bộ interface (`FromQuery`, `WithHeadings`, `WithMapping`, `WithChunkReading`, `PrimaryKey`, `WithReadingStrategy`, `WithCountTotal`, `WithCustomCsvSettings`), nên `ExportService` luôn gọi thẳng các method (`chunkSize()`, `primaryKey()`, `readingStrategy()`, `shouldCountTotal()`, `getCsvSettings()`) mà không cần kiểm tra `class_implements`/`in_array`.

- `query()`, `headings()`, `map()`: bắt buộc mỗi class export tự viết.
- `chunkSize()`, `primaryKey()`, `readingStrategy()`, `shouldCountTotal()`, `getCsvSettings()`: có default đọc từ `config/export.php` (config này đọc từ ENV, xem mục 4) — chỉ cần override lại trong class export khi muốn giá trị khác riêng cho export đó.

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

### 4. Cấu hình mặc định (`config/export.php`)

Publish file config ra project để chỉnh:

```bash
php artisan vendor:publish --tag=file-export-config
```

```php
return [
    'chunk_size' => (int) env('EXPORT_CHUNK_SIZE', 1000),
    'primary_key' => env('EXPORT_PRIMARY_KEY', 'id'),
    'reading_strategy' => env('EXPORT_READING_STRATEGY', 'cursor'), // 'cursor' | 'chunkById'
    'count_total' => env('EXPORT_COUNT_TOTAL', true),
    'records_per_file' => (int) env('EXPORT_RECORDS_PER_FILE', 50000), // chỉ áp dụng khi export dạng zip
    'csv' => [
        'delimiter' => env('EXPORT_CSV_DELIMITER', ','),
        'enclosure' => env('EXPORT_CSV_ENCLOSURE', ''),
    ],
];
```

Set biến tương ứng trong `.env` của từng project để đổi mặc định (vd `EXPORT_CHUNK_SIZE=5000`), không có thì dùng giá trị cố định ở trên. Từng class export vẫn override riêng được bằng cách viết đè method tương ứng trong `BaseExport` (`chunkSize()`, `primaryKey()`, `readingStrategy()`, `shouldCountTotal()`, `getCsvSettings()`).
