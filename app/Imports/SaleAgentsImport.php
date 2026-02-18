<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SaleAgentsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    public function model(array $row): ?Employee
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            return null;
        }
        return new Employee([
            'name' => $name,
            'email' => $row['email'] ?? null,
            'phone_number' => $row['phone_number'] ?? null,
            'department_id' => (int) ($row['department_id'] ?? 1),
            'designation_id' => isset($row['designation_id']) ? (int) $row['designation_id'] : null,
            'shift_id' => isset($row['shift_id']) ? (int) $row['shift_id'] : null,
            'staff_id' => $row['staff_id'] ?? null,
            'address' => $row['address'] ?? null,
            'city' => $row['city'] ?? null,
            'country' => $row['country'] ?? null,
            'basic_salary' => isset($row['basic_salary']) ? (float) $row['basic_salary'] : 0,
            'is_sale_agent' => true,
            'sale_commission_percent' => isset($row['sale_commission_percent']) ? (float) $row['sale_commission_percent'] : null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
