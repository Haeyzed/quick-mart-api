<?php

declare(strict_types=1);

namespace App\Http\Requests\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class GeneratePayrollDataRequest
 *
 * Handles validation and authorization for fetching prospective payroll calculation data.
 */
class GeneratePayrollDataRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            /**
             * The target month and year for payroll generation.
             *
             * @example 2024-12
             */
            'month' => ['required', 'date_format:Y-m'],

            /**
             * Optional warehouse ID to filter employees by specific branch.
             *
             * @example 1
             */
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],

            /**
             * Optional array of specific employee IDs to generate for.
             *
             * @example [1, 2, 3]
             */
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ];
    }
}
