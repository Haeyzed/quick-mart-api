<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Tax;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Tax entities with batching and upsert support.
 */
class TaxesImport implements
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
     * @return Tax|null
     */
    public function model(array $row): ?Tax
    {
        $name = trim((string)($row['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        return new Tax([
            'name' => $name,
            'rate' => !empty($row['rate']) ? (float)$row['rate'] : 0.0,
            'woocommerce_tax_id' => !empty($row['woocommerce_tax_id']) ? (int)$row['woocommerce_tax_id'] : null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
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
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'woocommerce_tax_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ];
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
