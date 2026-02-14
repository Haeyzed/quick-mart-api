<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * WarehousesImport
 *
 * Handles importing warehouses from CSV/Excel files.
 */
class WarehousesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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

            $name = trim((string)($row['name'] ?? ''));
            $phone = trim((string)($row['phone'] ?? '')) ?: null;
            $email = trim((string)($row['email'] ?? '')) ?: null;
            $address = trim((string)($row['address'] ?? '')) ?: null;

            // Find or create warehouse
            $warehouse = Warehouse::firstOrNew(
                ['name' => $name, 'is_active' => true]
            );

            $warehouse->name = $name;
            $warehouse->phone = $phone;
            $warehouse->email = $email;
            $warehouse->address = $address;
            $warehouse->is_active = true;

            $warehouse->save();
        }
    }
}
