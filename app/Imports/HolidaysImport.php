<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class HolidaysImport
 *
 * Handles the logic for importing holiday records from an uploaded Excel or CSV file.
 * Processes rows in batches and chunks to optimize memory usage.
 */
class HolidaysImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /**
     * Map a row from the spreadsheet to a Holiday model.
     *
     * @param array<string, mixed> $row
     * @return Holiday
     */
    public function model(array $row): Holiday
    {
        return new Holiday([
            'user_id' => Auth::id(), // Assign to user running the import
            'from_date' => Carbon::parse($row['from_date'])->format('Y-m-d'),
            'to_date' => Carbon::parse($row['to_date'])->format('Y-m-d'),
            'note' => $row['note'] ?? null,
            'recurring' => filter_var($row['recurring'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'region' => $row['region'] ?? null,
            'is_approved' => filter_var($row['is_approved'] ?? false, FILTER_VALIDATE_BOOLEAN),
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
             * Validate the starting date.
             */
            'from_date' => ['required', 'date'],

            /**
             * Validate the ending date, ensuring it is after or equal to the start date.
             */
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],

            /**
             * Validate the optional note.
             */
            'note' => ['nullable', 'string', 'max:500'],

            /**
             * Validate the recurring flag.
             */
            'recurring' => ['nullable', 'boolean'],

            /**
             * Validate the regional scope.
             */
            'region' => ['nullable', 'string', 'max:255'],

            /**
             * Validate the approval status.
             */
            'is_approved' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Determine the batch size for database inserts.
     * * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Determine the chunk size for reading the spreadsheet.
     * * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
