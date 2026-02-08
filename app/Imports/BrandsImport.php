<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class BrandsImport
 * * This version uses OnEachRow to explicitly perform an updateOrCreate logic.
 * It guarantees that if a brand name exists, it will be updated; otherwise, created.
 */
class BrandsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Process each row individually to ensure "First or Create" logic.
     *
     * @param Row $row
     * @return void
     */
    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $name = trim((string) ($data['name'] ?? ''));

        if (empty($name)) {
            return;
        }

        // Explicit FirstOrCreate/Update logic
        Brand::updateOrCreate(
            ['name' => $name], // Search criteria
            [
                'short_description' => trim((string) ($data['short_description'] ?? '')),
                'image_url'         => trim((string) ($data['image_url'] ?? '')) ?: null,
                'page_title'        => trim((string) ($data['page_title'] ?? '')) ?: null,
                'is_active'         => true,
            ]
        );
    }

    /**
     * Validation rules for the import.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
        ];
    }
}