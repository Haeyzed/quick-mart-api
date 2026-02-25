<?php

declare(strict_types=1);

namespace App\Http\Requests\Payrolls;

use App\Enums\PayrollStatusEnum;
use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Class UpdatePayrollRequest
 *
 * Handles validation and authorization for updating an existing payroll record.
 */
class UpdatePayrollRequest extends FormRequest
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
     * Prepare the data for validation.
     * * Formats the 'is_agent' flag into a proper boolean before rules are applied.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_agent')) {
            $this->merge([
                'is_agent' => filter_var($this->is_agent, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Payroll|null $payroll */
        $payroll = $this->route('payroll');

        return [
            /**
             * The optional reference number. Must be unique across all payrolls except the current one.
             *
             * @example PR-20241201-XYZ12
             */
            'reference_no' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('payrolls', 'reference_no')->ignore($payroll)->withoutTrashed()
            ],

            /**
             * The ID of the employee receiving the payroll.
             *
             * @example 5
             */
            'employee_id' => ['sometimes', 'required', 'integer', Rule::exists('employees', 'id')],

            /**
             * The ID of the account used for payment.
             *
             * @example 2
             */
            'account_id' => ['sometimes', 'required', 'integer', Rule::exists('accounts', 'id')],

            /**
             * The total net amount of the payroll.
             *
             * @example 1500.50
             */
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],

            /**
             * The method of payment (e.g., Cash, Bank Transfer).
             *
             * @example Bank Transfer
             */
            'paying_method' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * Any specific notes regarding this payroll update.
             *
             * @example Adjusted overtime amount based on manual review.
             */
            'note' => ['nullable', 'string', 'max:1000'],

            /**
             * The status of the payroll. Must be a valid enum value.
             *
             * @example paid
             */
            'status' => ['sometimes', 'required', new Enum(PayrollStatusEnum::class)],

            /**
             * The month and year the payroll is for.
             *
             * @example 12-2024
             */
            'month' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * Override the default creation/issue date.
             *
             * @example 2024-12-01
             */
            'created_at' => ['nullable', 'date'],

            /**
             * The base salary amount used for calculations.
             *
             * @example 1000.00
             */
            'salary_amount' => ['nullable', 'numeric', 'min:0'],

            /**
             * Previous expenses or deductions to be factored into the payroll.
             *
             * @example 50.00
             */
            'expense' => ['nullable', 'numeric', 'min:0'],

            /**
             * Fixed commission amount.
             *
             * @example 100.00
             */
            'commission' => ['nullable', 'numeric', 'min:0'],

            /**
             * Indicates if the employee is a sales agent (triggers percentage-based commission calculation).
             *
             * @example true
             */
            'is_agent' => ['nullable', 'boolean'],

            /**
             * Commission percentage to calculate against the base salary.
             *
             * @example 5.5
             */
            'commission_percent' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
