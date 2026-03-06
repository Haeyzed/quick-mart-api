<?php

declare(strict_types=1);

namespace App\Http\Requests\Employees;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class StoreEmployeeRequest
 *
 * Handles validation and authorization for creating a new employee record.
 * Supports deeply nested arrays for creating associated Users, Profiles, Documents, and Onboarding.
 */
class StoreEmployeeRequest extends BaseRequest
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
        return [
            // ==========================================
            // ROOT EMPLOYEE DETAILS
            // ==========================================
            'name' => ['required', 'string', 'max:255'],
            'staff_id' => ['required', 'string', 'max:100', Rule::unique('employees', 'staff_id')->withoutTrashed()],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->withoutTrashed()],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'designation_id' => ['required', 'integer', 'exists:designations,id'],
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],

            // ==========================================
            // EMPLOYEE SPECIFIC DETAILS (Nested under 'employee')
            // ==========================================
            'employee' => ['nullable', 'array'],
            'employee.employment_type_id' => ['nullable', 'integer', 'exists:employment_types,id'],
            'employee.joining_date' => ['nullable', 'date'],
            'employee.confirmation_date' => ['nullable', 'date'],
            'employee.probation_end_date' => ['nullable', 'date'],
            'employee.reporting_manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'employee.warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'employee.work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'],
            'employee.salary_structure_id' => ['nullable', 'integer', 'exists:salary_structures,id'],
            'employee.employment_status' => ['nullable', 'string', 'max:50'],
            'employee.is_sale_agent' => ['nullable', 'boolean'],
            'employee.sale_commission_percent' => ['nullable', 'numeric', 'min:0'],
            'employee.sales_target' => ['nullable', 'array'],
            'employee.sales_target.*.sales_from' => ['required_with:employee.sales_target', 'numeric', 'min:0'],
            'employee.sales_target.*.sales_to' => ['required_with:employee.sales_target', 'numeric', 'gt:employee.sales_target.*.sales_from'],
            'employee.sales_target.*.percent' => ['required_with:employee.sales_target', 'numeric', 'min:0', 'max:100'],

            // ==========================================
            // NESTED USER ACCOUNT SYNC VALIDATION
            // ==========================================
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'user' => ['nullable', 'array'],
            'user.username' => [
                'required_with:user',
                'string',
                'max:255',
                Rule::unique('users', 'username')->withoutTrashed()
            ],
            'user.password' => ['required_with:user', 'string', 'min:8'],
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
            // NESTED INITIAL DOCUMENTS ATTACHMENT
            // ==========================================
            'documents' => ['nullable', 'array'],
            'documents.*.document_type_id' => ['required_with:documents', 'integer', 'exists:document_types,id'],
            'documents.*.file' => ['required_with:documents', 'file', 'mimes:pdf,jpeg,png,jpg,webp', 'max:5120'],
            'documents.*.name' => ['nullable', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
            'documents.*.issue_date' => ['nullable', 'date'],
            'documents.*.expiry_date' => ['nullable', 'date'],

            // ==========================================
            // ONBOARDING TRIGGER
            // ==========================================
            'onboarding_checklist_template_id' => ['nullable', 'integer', 'exists:onboarding_checklist_templates,id'],
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
            $salesTargets = $this->input('employee.sales_target');

            if (is_array($salesTargets) && count($salesTargets) > 0) {
                $previousTo = null;

                foreach ($salesTargets as $index => $target) {
                    $from = (float)($target['sales_from'] ?? 0);
                    $to = (float)($target['sales_to'] ?? 0);

                    if ($previousTo !== null && $from <= $previousTo) {
                        $validator->errors()->add(
                            "employee.sales_target.{$index}.sales_from",
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
     * Formats the boolean flags before rules are applied.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->has('is_active')) {
            $merge['is_active'] = filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN);
        }

        // Handle nested employee boolean fields
        if ($this->has('employee')) {
            $employeeData = $this->input('employee');
            if (isset($employeeData['is_sale_agent'])) {
                $employeeData['is_sale_agent'] = filter_var($employeeData['is_sale_agent'], FILTER_VALIDATE_BOOLEAN);
                $this->merge(['employee' => $employeeData]);
            }
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}
