<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class OvertimesImport
 *
 * Handles the logic for importing overtime records from an uploaded Excel or CSV file.
 * Processes rows in batches and chunks to optimize memory usage.
 */
class OvertimesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /**
     * Map a row from the spreadsheet to an Overtime model.
     *
     * @param array<string, mixed> $row
     * @return Overtime
     */
    public function model(array $row): Overtime
    {
        // Calculate the overtime amount dynamically during import
        $employee = Employee::query()->find($row['employee_id']);
        $amount = $employee ? ($employee->monthly_salary / 30 / 8) * (float) $row['hours'] : 0;

        return new Overtime([
            'employee_id' => $row['employee_id'],
            'date' => \Carbon\Carbon::parse($row['date'])->format('Y-m-d'),
            'hours' => (float) $row['hours'],
            'amount' => $amount,
            'status' => $row['status'] ?? 'Pending',
            'approved_by' => Auth::id(),
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
             * Validate that the employee exists in the database.
             */
            'employee_id' => ['required', 'integer', 'exists:employees,id'],

            /**
             * Validate the date of the overtime.
             */
            'date' => ['required', 'date'],

            /**
             * Validate the total hours worked.
             */
            'hours' => ['required', 'numeric', 'min:0'],

            /**
             * Validate the status if provided.
             */
            'status' => ['nullable', 'string', 'in:Pending,Approved,Rejected'],
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
