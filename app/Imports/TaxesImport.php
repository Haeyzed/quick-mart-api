<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Tax;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * TaxesImport
 *
 * Handles importing taxes from CSV/Excel files.
 */
class TaxesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
            // Skip if name is empty
            if (empty($row['name'] ?? null)) {
                continue;
            }

            $name = trim($row['name'] ?? '');
            $rate = !empty($row['rate'] ?? null) ? (float)$row['rate'] : 0;

            // Find or create tax
            $tax = Tax::firstOrNew(
                ['name' => $name, 'is_active' => true]
            );

            $tax->name = $name;
            $tax->rate = $rate;
            $tax->is_active = true;

            $tax->save();
        }
    }
}
