<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class DepartmentsImport
 *
 * Handles the logic for importing department records from an uploaded Excel or CSV file.
 * Processes rows in batches and chunks to optimize memory usage.
 * Utilizes upserts to create, update, or restore soft-deleted records automatically.
 */
class DepartmentsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    WithUpserts,
    SkipsEmptyRows
{
    /**
     * Map a row from the spreadsheet to a Department model.
     * Setting 'deleted_at' to null ensures that if the record was previously soft-deleted,
     * it will be automatically restored during the upsert.
     *
     * @param array<string, mixed> $row
     * @return Department|null
     */
    public function model(array $row): ?Department
    {
        $name = trim((string)($row['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        return new Department([
            'name' => $name,
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'deleted_at' => null,
        ]);
    }

    /**
     * Specify the unique column to be used for the upsert operation.
     *
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'name';
    }

    /**
     * Define the validation rules for the imported rows.
     * Note: The 'unique' rule has been removed so that existing or soft-deleted
     * records can pass validation and proceed to the upsert/restore logic.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * Validate that the department name is present.
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * Validate the active status if provided.
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Determine the batch size for database inserts.
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Determine the chunk size for reading the spreadsheet.
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
