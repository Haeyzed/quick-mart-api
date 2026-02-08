<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Tax;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * Excel/CSV import for Tax entities.
 *
 * Uses upsert logic: creates new taxes or updates existing ones by name.
 * Skips empty rows.
 */
class TaxesImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Process a single row from the import file.
     *
     * Skips rows with empty name. Uses updateOrCreate on name for upsert behavior.
     *
     * @param Row $row The current row being imported.
     */
    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            return;
        }

        $rate = ! empty($data['rate'] ?? null) ? (float) $data['rate'] : 0.0;

        Tax::updateOrCreate(
            ['name' => $name],
            [
                'rate' => $rate,
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
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
