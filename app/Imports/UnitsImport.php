<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Unit entities with batching and upsert support.
 */
class UnitsImport implements
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
     * @return Unit|null
     */
    public function model(array $row): ?Unit
    {
        $code = trim((string)($row['code'] ?? ''));

        if ($code === '') {
            return null;
        }

        $baseUnitId = null;
        $baseUnitCode = trim((string)($row['base_unit_code'] ?? ''));

        if ($baseUnitCode !== '') {
            $baseUnitId = Unit::where('code', $baseUnitCode)->value('id');
        }

        return new Unit([
            'code' => $code,
            'name' => trim((string)($row['name'] ?? $code)),
            'base_unit' => $baseUnitId,
            'operator' => $row['operator'] ?? ($baseUnitId ? '*' : null),
            'operation_value' => isset($row['operation_value']) ? (float)$row['operation_value'] : ($baseUnitId ? 1.0 : null),
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    /**
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'code';
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'base_unit_code' => ['nullable', 'string', 'exists:units,code'],
            'operator' => ['nullable', 'string', 'in:*,/,+,-'],
            'operation_value' => ['nullable', 'numeric', 'min:0'],
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
