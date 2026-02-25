<?php

declare(strict_types=1);

namespace App\Http\Requests\Payrolls;

use App\Enums\PayrollStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class BulkStorePayrollRequest
 *
 * Handles validation and authorization for processing a bulk array of calculated payrolls.
 */
class BulkStorePayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * The month and year the payrolls belong to.
             *
             * @example 2024-12
             */
            'month' => ['required', 'string'],

            /**
             * The global account ID to deduct funds from if status is paid.
             *
             * @example 1
             */
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],

            /**
             * The global status to apply to all submitted payrolls.
             *
             * @example paid
             */
            'payroll_group_status' => ['required', new Enum(PayrollStatusEnum::class)],

            /**
             * An array of individual payroll data objects to process.
             */
            'payrolls' => ['required', 'array', 'min:1'],
            'payrolls.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'payrolls.*.amount' => ['required', 'numeric'],
            'payrolls.*.commission' => ['nullable', 'numeric'],
            'payrolls.*.expense' => ['nullable', 'numeric'],
            'payrolls.*.overtime' => ['nullable', 'numeric'],
            'payrolls.*.paying_method' => ['nullable', 'string'],
            'payrolls.*.note' => ['nullable', 'string'],
        ];
    }
}
