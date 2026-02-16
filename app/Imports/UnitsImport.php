<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Unit entities.
 *
 * Uses ToCollection to handle self-referencing dependencies (base units)
 * by ensuring base units are processed before sub-units.
 */
class UnitsImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsEmptyRows
{
    /**
     * Process the collection of rows.
     *
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        $sortedRows = $collection->sortBy(fn($row) => !empty($row['base_unit_code']));

        foreach ($sortedRows as $row) {
            $code = trim((string)($row['code'] ?? ''));

            if ($code === '') {
                continue;
            }

            $baseUnitId = null;
            $baseUnitCode = trim((string)($row['base_unit_code'] ?? ''));

            if ($baseUnitCode !== '') {
                $baseUnitId = Unit::query()->where('code', $baseUnitCode)->value('id');
            }

            Unit::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => trim((string)($row['name'] ?? $code)),
                    'base_unit' => $baseUnitId,
                    'operator' => $row['operator'] ?? ($baseUnitId ? '*' : null),
                    'operation_value' => isset($row['operation_value']) ? (float)$row['operation_value'] : ($baseUnitId ? 1.0 : null),
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                ]
            );
        }
    }

    /**
     * Get validation rules for each row.
     *
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'base_unit_code' => ['nullable', 'string'],
            'operator' => ['nullable', 'string', 'in:*,/,+,-'],
            'operation_value' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
