<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\LeaveType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LeaveTypesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row): LeaveType
    {
        return new LeaveType([
            'name' => $row['name'],
            'annual_quota' => (float) $row['annual_quota'],
            'encashable' => filter_var($row['encashable'], FILTER_VALIDATE_BOOLEAN),
            'carry_forward_limit' => (float) $row['carry_forward_limit'],
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:leave_types,name'],
            'annual_quota' => ['required', 'numeric', 'min:0'],
            'encashable' => ['required', 'boolean'],
            'carry_forward_limit' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
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
