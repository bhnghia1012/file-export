<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chunk size
    |--------------------------------------------------------------------------
    | Số record đọc mỗi lần khi dùng reading strategy "chunkById".
    | Override per-export bằng cách override BaseExport::chunkSize().
    */
    'chunk_size' => (int) env('EXPORT_CHUNK_SIZE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Primary key
    |--------------------------------------------------------------------------
    | Cột dùng để chunkById.
    | Override per-export bằng cách override BaseExport::primaryKey().
    */
    'primary_key' => env('EXPORT_PRIMARY_KEY', 'id'),

    /*
    |--------------------------------------------------------------------------
    | Reading strategy
    |--------------------------------------------------------------------------
    | "cursor" hoặc "chunkById".
    | Override per-export bằng cách override BaseExport::readingStrategy().
    */
    'reading_strategy' => env('EXPORT_READING_STRATEGY', 'cursor'),

    /*
    |--------------------------------------------------------------------------
    | Count total
    |--------------------------------------------------------------------------
    | Có đếm & cache tổng số record trước khi export hay không.
    | Override per-export bằng cách override BaseExport::shouldCountTotal().
    */
    'count_total' => env('EXPORT_COUNT_TOTAL', true),

    /*
    |--------------------------------------------------------------------------
    | Records per file (chỉ áp dụng cho export dạng zip)
    |--------------------------------------------------------------------------
    | Số record tối đa mỗi file .tsv trước khi tách sang file mới.
    */
    'records_per_file' => (int) env('EXPORT_RECORDS_PER_FILE', 50000),

    /*
    |--------------------------------------------------------------------------
    | CSV settings
    |--------------------------------------------------------------------------
    | Override per-export bằng cách override BaseExport::getCsvSettings().
    */
    'csv' => [
        'delimiter' => env('EXPORT_CSV_DELIMITER', ','),
        'enclosure' => env('EXPORT_CSV_ENCLOSURE', ''),
    ],
];
