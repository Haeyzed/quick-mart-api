<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\CustomerGroup;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * CustomerGroupsImport
 *
 * Handles importing customer groups from CSV/Excel files.
 */
class CustomerGroupsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
            $percentage = !empty($row['percentage'] ?? null) ? (float)$row['percentage'] : 0;

            // Find or create customer group
            $customerGroup = CustomerGroup::firstOrNew(
                ['name' => $name, 'is_active' => true]
            );

            $customerGroup->name = $name;
            $customerGroup->percentage = $percentage;
            $customerGroup->is_active = true;

            $customerGroup->save();
        }
    }
}
