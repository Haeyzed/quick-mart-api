<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class DepartmentsImport
 *
 * Handles the logic for importing department records from an uploaded Excel or CSV file.
 * Processes rows in batches and chunks to optimize memory usage.
 */
class DepartmentsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /**
     * Map a row from the spreadsheet to a Department model.
     *
     * @param array<string, mixed> $row
     * @return Department
     */
    public function model(array $row): Department
    {
        return new Department([
            'name' => $row['name'],
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Define the validation rules for the imported rows.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * Validate that the department name is unique.
             */
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],

            /**
             * Validate the active status if provided.
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Determine the batch size for database inserts.
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Determine the chunk size for reading the spreadsheet.
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
