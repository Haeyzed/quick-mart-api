<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class BrandsImport implements ToModel, WithHeadingRow, WithValidation, WithUpserts, WithBatchInserts, WithChunkReading
{
    /**
     * @param array<string, mixed> $row
     * @return Brand|null
     */
    public function model(array $row): ?Brand
    {
        $name = trim($row['name'] ?? '');

        if ($name === '') {
            return null;
        }

        // Note: upsert requires explicit definition of fields
        return new Brand([
            'name'              => $name,
            'slug'              => Str::slug($name),
            'short_description' => $row['short_description'] ?? null,
            'page_title'        => $row['page_title'] ?? null,
            'image_url'         => $row['image_url'] ?? null,
            'is_active'         => true,
        ]);
    }

    public function uniqueBy(): string
    {
        return 'name';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
            'short_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
