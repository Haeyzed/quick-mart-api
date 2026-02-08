<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class BrandsImport
 * * High-performance import using upserts (Update on collision, Insert on new).
 */
class BrandsImport implements 
    ToModel, 
    WithHeadingRow, 
    SkipsEmptyRows, 
    WithBatchInserts, 
    WithUpserts,
    WithValidation
{
    /**
     * Transform row into Model instance.
     * * @param array $row
     * @return Model|null
     */
    public function model(array $row): ?Model
    {
        return new Brand([
            'name'              => trim((string) ($row['name'] ?? '')),
            'short_description' => trim((string) ($row['short_description'] ?? '')),
            'image_url'         => trim((string) ($row['image_url'] ?? '')) ?: null,
            'page_title'        => trim((string) ($row['page_title'] ?? '')) ?: null,
            'is_active'         => true,
        ]);
    }

    /**
     * Unique key for Upsert logic.
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'name';
    }

    /**
     * Bulk validation rules.
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
        ];
    }

    /**
     * Rows per database trip.
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }
}