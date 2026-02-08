<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * Excel/CSV import for Unit entities.
 *
 * Uses upsert logic: creates new units or updates existing ones by code.
 * Base unit lookup by code. Skips empty rows.
 */
class UnitsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Process a single row from the import file.
     *
     * Skips rows with empty code. Uses updateOrCreate on code for upsert behavior.
     *
     * @param Row $row The current row being imported.
     */
    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $code = trim((string) ($data['code'] ?? ''));

        if ($code === '') {
            return;
        }

        $name = trim((string) ($data['name'] ?? ''));
        $baseUnitCode = trim((string) ($data['baseunit'] ?? ''));
        $operator = trim((string) ($data['operator'] ?? '*'));
        $operationValue = ! empty($data['operationvalue'] ?? null) ? (float) $data['operationvalue'] : 1.0;

        $baseUnitId = null;
        if ($baseUnitCode !== '') {
            $baseUnit = Unit::where('code', $baseUnitCode)->first();
            if ($baseUnit) {
                $baseUnitId = $baseUnit->id;
            }
        }

        if ($baseUnitId === null) {
            $operator = '*';
            $operationValue = 1;
        }

        Unit::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name ?: $code,
                'base_unit' => $baseUnitId,
                'operator' => $operator,
                'operation_value' => $operationValue,
                'is_active' => true,
            ]
        );
    }

    /**
     * Get validation rules for each row.
     *
     * @return array<string, array<int, string>> Validation rules keyed by column heading.
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'baseunit' => ['nullable', 'string', 'max:255'],
            'operator' => ['nullable', 'string', 'in:*,/,+,-'],
            'operationvalue' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
