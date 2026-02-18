<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Brand entities with batching and upsert support.
 */
class BrandsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    /**
     * @param array<string, mixed> $row
     * @return Brand|null
     */
    public function model(array $row): ?Brand
    {
        $name = trim((string)($row['name'] ?? ''));

        return new Brand([
            'name' => $name,
            'slug' => Str::slug($name),
            'page_title' => $row['page_title'] ?? null,
            'short_description' => $row['short_description'] ?? null,
            'image_url' => $row['image_url'] ?? null,
            'is_active' => $this->parseBoolean($row['is_active'] ?? true),
        ]);
    }

    /**
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'name';
    }

    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable'],
        ];
    }

    /**
     * Helper to handle various boolean formats from Excel (1/0, true/false, "yes"/"no")
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
