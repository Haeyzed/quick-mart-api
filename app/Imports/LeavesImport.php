<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LeavesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row): Leave
    {
        $start = Carbon::parse($row['start_date']);
        $end = Carbon::parse($row['end_date']);
        $days = $start->diffInDays($end) + 1;

        return new Leave([
            'employee_id' => $row['employee_id'],
            'leave_type_id' => $row['leave_type_id'],
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'days' => $days,
            'status' => $row['status'] ?? 'Pending',
            'approver_id' => Auth::id(),
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'in:Pending,Approved,Rejected'],
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
