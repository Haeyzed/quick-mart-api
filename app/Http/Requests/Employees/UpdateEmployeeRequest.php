<?php

declare(strict_types=1);

namespace App\Http\Requests\Employees;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateEmployeeRequest
 *
 * Handles validation and authorization for updating an existing employee record.
 */
class UpdateEmployeeRequest extends FormRequest
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
     * Formats the boolean flags before rules are applied.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->has('is_active')) {
            $merge['is_active'] = filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->has('is_sale_agent')) {
            $merge['is_sale_agent'] = filter_var($this->is_sale_agent, FILTER_VALIDATE_BOOLEAN);
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Employee|null $employee */
        $employee = $this->route('employee');

        return [
            /**
             * The full name of the employee.
             *
             * @example Jane Doe
             */
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * The unique staff ID or employee code (excluding the current record).
             *
             * @example EMP-001
             */
            'staff_id' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('employees', 'staff_id')->ignore($employee)->withoutTrashed(),
            ],

            /**
             * The email address for the employee (excluding the current record).
             *
             * @example janedoe@example.com
             */
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee)->withoutTrashed(),
            ],

            /**
             * The phone number of the employee.
             *
             * @example +1234567890
             */
            'phone_number' => ['nullable', 'string', 'max:255'],

            /**
             * The ID of the associated department.
             *
             * @example 1
             */
            'department_id' => ['sometimes', 'required', 'integer', 'exists:departments,id'],

            /**
             * The ID of the associated designation.
             *
             * @example 2
             */
            'designation_id' => ['sometimes', 'required', 'integer', 'exists:designations,id'],

            /**
             * The ID of the associated shift.
             *
             * @example 1
             */
            'shift_id' => ['sometimes', 'required', 'integer', 'exists:shifts,id'],

            /**
             * The basic salary amount for the employee.
             *
             * @example 5000.00
             */
            'basic_salary' => ['sometimes', 'required', 'numeric', 'min:0'],

            /**
             * The optional street address.
             *
             * @example 123 Main Street
             */
            'address' => ['nullable', 'string', 'max:255'],

            /**
             * The associated country ID.
             *
             * @example 1
             */
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],

            /**
             * The associated state ID.
             *
             * @example 12
             */
            'state_id' => ['nullable', 'integer', 'exists:states,id'],

            /**
             * The associated city ID.
             *
             * @example 45
             */
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],

            /**
             * The optional image or avatar for the employee.
             */
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ],

            /**
             * Determines if the employee is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * Determines if the employee acts as a sales agent.
             *
             * @example false
             */
            'is_sale_agent' => ['nullable', 'boolean'],

            /**
             * Global commission percentage for the agent.
             *
             * @example 5.5
             */
            'sale_commission_percent' => ['nullable', 'numeric', 'min:0'],

            /**
             * Tiered commission array for the agent.
             */
            'sales_target' => ['nullable', 'array'],
        ];
    }
}
