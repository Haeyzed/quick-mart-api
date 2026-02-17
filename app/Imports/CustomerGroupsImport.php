<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\CustomerGroup;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Customer Group entities with batching and upsert support.
 */
class CustomerGroupsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?CustomerGroup
    {
        $name = trim((string) ($row['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        $percentage = isset($row['percentage']) ? (float) $row['percentage'] : 0.0;
        $isActive = isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true;

        return new CustomerGroup([
            'name' => $name,
            'percentage' => $percentage,
            'is_active' => $isActive,
        ]);
    }

    public function uniqueBy(): string
    {
        return 'name';
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
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
