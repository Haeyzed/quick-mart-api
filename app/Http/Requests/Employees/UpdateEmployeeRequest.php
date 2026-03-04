<?php

declare(strict_types=1);

namespace App\Http\Requests\Employees;

use App\Http\Requests\BaseRequest;
use App\Models\Employee;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class UpdateEmployeeRequest
 *
 * Handles validation and authorization for updating an existing employee record.
 * Supports updating associated User accounts, Profiles, and attaching/updating Documents.
 */
class UpdateEmployeeRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Employee|null $employee */
        $employee = $this->route('employee');

        return [
            // ==========================================
            // ROOT EMPLOYEE DETAILS
            // ==========================================
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'staff_id' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('employees', 'staff_id')->ignore($employee)->withoutTrashed()
            ],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($employee?->user_id)->withoutTrashed()
            ],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'department_id' => ['sometimes', 'required', 'integer', 'exists:departments,id'],
            'designation_id' => ['sometimes', 'required', 'integer', 'exists:designations,id'],
            'shift_id' => ['sometimes', 'required', 'integer', 'exists:shifts,id'],
            'basic_salary' => ['sometimes', 'required', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],

            // ==========================================
            // EMPLOYMENT STATUS & RELATIONS
            // ==========================================
            'employment_type_id' => ['nullable', 'integer', 'exists:employment_types,id'],
            'joining_date' => ['nullable', 'date'],
            'confirmation_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date'],
            'reporting_manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'],
            'salary_structure_id' => ['nullable', 'integer', 'exists:salary_structures,id'],
            'employment_status' => ['nullable', 'string', 'max:50'],

            // ==========================================
            // SALES AGENT SETTINGS
            // ==========================================
            'is_sale_agent' => ['nullable', 'boolean'],
            'sale_commission_percent' => ['nullable', 'numeric', 'min:0'],
            'sales_target' => ['nullable', 'array'],
            'sales_target.*.sales_from' => ['required_with:sales_target', 'numeric', 'min:0'],
            'sales_target.*.sales_to' => ['required_with:sales_target', 'numeric', 'gt:sales_target.*.sales_from'],
            'sales_target.*.percent' => ['required_with:sales_target', 'numeric', 'min:0', 'max:100'],

            // ==========================================
            // NESTED USER ACCOUNT SYNC VALIDATION
            // ==========================================
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'user' => ['nullable', 'array'],
            'user.username' => [
                'nullable', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($employee?->user_id)->withoutTrashed()
            ],
            'user.password' => ['nullable', 'string', 'min:8'],
            'user.roles' => ['nullable', 'array'],
            'user.roles.*' => ['integer', 'exists:roles,id'],
            'user.permissions' => ['nullable', 'array'],
            'user.permissions.*' => ['integer', 'exists:permissions,id'],

            // ==========================================
            // NESTED PROFILE VALIDATION (PII)
            // ==========================================
            'profile' => ['nullable', 'array'],
            'profile.date_of_birth' => ['nullable', 'date'],
            'profile.gender' => ['nullable', 'string', 'in:male,female,other'],
            'profile.marital_status' => ['nullable', 'string'],
            'profile.national_id' => ['nullable', 'string', 'max:100'],
            'profile.tax_number' => ['nullable', 'string', 'max:100'],
            'profile.bank_name' => ['nullable', 'string', 'max:255'],
            'profile.account_number' => ['nullable', 'string', 'max:100'],
            'profile.emergency_contact' => ['nullable', 'array'],

            // ==========================================
            // NESTED DOCUMENTS UPDATE / ATTACHMENT
            // ==========================================
            'documents' => ['nullable', 'array'],
            'documents.*.id' => ['nullable', 'integer', 'exists:employee_documents,id'], // ID determines Update vs Create
            'documents.*.document_type_id' => ['required_with:documents', 'integer', 'exists:document_types,id'],
            'documents.*.file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,webp', 'max:5120'], // Nullable during update
            'documents.*.name' => ['nullable', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
            'documents.*.issue_date' => ['nullable', 'date'],
            'documents.*.expiry_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     * Implements advanced cross-row validation for the sales_target array.
     *
     * @param Validator $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $salesTargets = $this->input('sales_target');

            if (is_array($salesTargets) && count($salesTargets) > 0) {
                $previousTo = null;

                foreach ($salesTargets as $index => $target) {
                    $from = (float)($target['sales_from'] ?? 0);
                    $to = (float)($target['sales_to'] ?? 0);

                    if ($previousTo !== null && $from <= $previousTo) {
                        $validator->errors()->add(
                            "sales_target.{$index}.sales_from",
                            "The sales from value must be greater than the previous tier's sales to value ({$previousTo})."
                        );
                    }

                    $previousTo = $to;
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
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
}
