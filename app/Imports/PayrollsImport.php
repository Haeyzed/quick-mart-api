<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class PayrollsImport
 *
 * Handles the logic for importing payroll records from an uploaded Excel or CSV file.
 * Processes rows in batches and chunks to optimize memory usage.
 */
class PayrollsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /**
     * Map a row from the spreadsheet to a Payroll model.
     *
     * @param array<string, mixed> $row
     * @return Payroll
     */
    public function model(array $row): Payroll
    {
        return new Payroll([
            'reference_no' => $row['reference_no'] ?? 'PR-' . date('Ymd') . '-' . Str::random(5),
            'employee_id' => $row['employee_id'],
            'account_id' => $row['account_id'],
            'user_id' => Auth::id(), // Assigned to the user uploading the file
            'amount' => (float) $row['amount'],
            'paying_method' => $row['paying_method'],
            'note' => $row['note'] ?? null,
            'status' => $row['status'] ?? 'draft',
            'month' => $row['month'],
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
            'reference_no' => ['nullable', 'string', 'unique:payrolls,reference_no'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paying_method' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'max:50'],
            'month' => ['required', 'string', 'max:255'],
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
