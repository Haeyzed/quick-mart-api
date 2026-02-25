<?php

declare(strict_types=1);

namespace App\Http\Requests\Payrolls;

use App\Enums\PayrollStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Class StorePayrollRequest
 *
 * Handles validation and authorization for creating a new payroll record.
 */
class StorePayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
             * The optional reference number. Automatically generated if null.
             *
             * @example PR-20241201-XYZ12
             */
            'reference_no' => ['nullable', 'string', 'max:255', Rule::unique('payrolls', 'reference_no')->withoutTrashed()],

            /**
             * The ID of the employee receiving the payroll.
             *
             * @example 5
             */
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],

            /**
             * The ID of the account used for payment.
             *
             * @example 2
             */
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')],

            /**
             * The total amount of the payroll.
             *
             * @example 1500.50
             */
            'amount' => ['required', 'numeric', 'min:0'],

            /**
             * The method of payment.
             *
             * @example Bank Transfer
             */
            'paying_method' => ['required', 'string', 'max:255'],

            /**
             * Any notes regarding this payroll.
             *
             * @example November Salary plus bonus
             */
            'note' => ['nullable', 'string', 'max:1000'],

            /**
             * The status of the payroll (e.g., draft, paid).
             *
             * @example paid
             */
            'status' => ['required', new Enum(PayrollStatusEnum::class)],

            /**
             * The month and year the payroll is for.
             *
             * @example 12-2024
             */
            'month' => ['required', 'string', 'max:255'],

            /**
             * Override the default creation date.
             *
             * @example 2024-12-01
             */
            'created_at' => ['nullable', 'date'],

            /**
             * The base salary amount used for calculations.
             *
             * @example 1000
             */
            'salary_amount' => ['nullable', 'numeric', 'min:0'],

            /**
             * Previous expenses or deductions.
             *
             * @example 50
             */
            'expense' => ['nullable', 'numeric', 'min:0'],

            /**
             * Fixed commission amount.
             *
             * @example 100
             */
            'commission' => ['nullable', 'numeric', 'min:0'],

            /**
             * Indicates if the employee is a sales agent (triggers percentage calculation).
             *
             * @example true
             */
            'is_agent' => ['nullable', 'boolean'],

            /**
             * Commission percentage to calculate against salary.
             *
             * @example 5.5
             */
            'commission_percent' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
