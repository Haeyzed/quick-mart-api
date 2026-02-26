<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Shift;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class ShiftsImport
 *
 * Handles the logic for importing shift records from an uploaded Excel or CSV file.
 * Processes rows in batches and chunks to optimize memory usage.
 * Utilizes upserts to create, update, or restore soft-deleted records automatically.
 */
class ShiftsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    WithUpserts,
    SkipsEmptyRows
{
    /**
     * Map a row from the spreadsheet to a Shift model.
     * Setting 'deleted_at' to null ensures that if the record was previously soft-deleted,
     * it will be automatically restored during the upsert.
     *
     * @param array<string, mixed> $row
     * @return Shift|null
     */
    public function model(array $row): ?Shift
    {
        $name = trim((string)($row['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        return new Shift([
            'name' => $name,
            'start_time' => Carbon::parse($row['start_time'])->format('H:i'),
            'end_time' => Carbon::parse($row['end_time'])->format('H:i'),
            'grace_in' => (int) ($row['grace_in'] ?? 0),
            'grace_out' => (int) ($row['grace_out'] ?? 0),
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
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * Validate that the shift name is present.
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * Validate the start time.
             */
            'start_time' => ['required'],

            /**
             * Validate the end time.
             */
            'end_time' => ['required'],

            /**
             * Validate grace_in (minutes).
             */
            'grace_in' => ['nullable', 'integer', 'min:0'],

            /**
             * Validate grace_out (minutes).
             */
            'grace_out' => ['nullable', 'integer', 'min:0'],

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
