<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * UnitsImport
 *
 * Handles importing units from CSV/Excel files.
 */
class UnitsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Process the collection of rows.
     *
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            // Skip if code is empty
            if (empty($row['code'] ?? null)) {
                continue;
            }

            $code = trim($row['code'] ?? '');
            $name = trim($row['name'] ?? '');
            $baseUnitCode = trim($row['baseunit'] ?? '');
            $operator = trim($row['operator'] ?? '*');
            $operationValue = !empty($row['operationvalue'] ?? null) ? (float)$row['operationvalue'] : 1;

            // Find base unit if provided
            $baseUnitId = null;
            if (!empty($baseUnitCode)) {
                $baseUnit = Unit::where('code', $baseUnitCode)->first();
                if ($baseUnit) {
                    $baseUnitId = $baseUnit->id;
                }
            }

            // If no base unit, set default operator and operation value
            if (!$baseUnitId) {
                $operator = '*';
                $operationValue = 1;
            }

            // Find or create unit
            $unit = Unit::firstOrNew(
                ['code' => $code, 'is_active' => true]
            );

            $unit->code = $code;
            $unit->name = $name;
            $unit->base_unit = $baseUnitId;
            $unit->operator = $operator;
            $unit->operation_value = $operationValue;
            $unit->is_active = true;

            $unit->save();
        }
    }
}
