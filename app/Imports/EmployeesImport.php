<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Employee entities with batching and upsert support.
 */
class EmployeesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    /**
     * Map a row from the spreadsheet to an Employee model.
     *
     * @param array<string, mixed> $row
     * @return Employee|null
     */
    public function model(array $row): ?Employee
    {
        $staffId = trim((string)($row['staff_id'] ?? ''));

        if ($staffId === '') {
            return null;
        }

        return new Employee([
            'staff_id' => $staffId,
            'name' => $row['name'] ?? null,
            'email' => trim((string)($row['email'] ?? '')) ?: null,
            'phone_number' => $row['phone_number'] ?? null,
            'department_id' => $row['department_id'] ?? null,
            'designation_id' => $row['designation_id'] ?? null,
            'shift_id' => $row['shift_id'] ?? null,
            'basic_salary' => (float) ($row['basic_salary'] ?? 0),
            'address' => $row['address'] ?? null,
            'city_id' => $row['city_id'] ?? null,
            'state_id' => $row['state_id'] ?? null,
            'country_id' => $row['country_id'] ?? null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            'is_sale_agent' => isset($row['is_sale_agent']) ? filter_var($row['is_sale_agent'], FILTER_VALIDATE_BOOLEAN) : false,
            'sale_commission_percent' => isset($row['sale_commission_percent']) ? (float) $row['sale_commission_percent'] : null,
            'image_url' => $row['image_url'] ?? null,
        ]);
    }

    /**
     * Upsert records using staff_id as the unique identifier.
     *
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'staff_id';
    }

    /**
     * Define the validation rules for the imported rows.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'staff_id' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'designation_id' => ['required', 'integer', 'exists:designations,id'],
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_sale_agent' => ['nullable', 'boolean'],
            'sale_commission_percent' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
