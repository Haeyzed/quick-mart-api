<?php

declare(strict_types=1);

namespace App\Http\Requests\Employees;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

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
        if ($this->has('is_sale_agent')) {
            $merge['is_sale_agent'] = filter_var($this->is_sale_agent, FILTER_VALIDATE_BOOLEAN);
        }
        if (! empty($merge)) {
            $this->merge($merge);
        }
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
            'profile.gender' => ['nullable', 'string', 'in:Male,Female,Other'],
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
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $salesTargets = $this->input('sales_target');

            if (is_array($salesTargets) && count($salesTargets) > 0) {
                $previousTo = null;

                foreach ($salesTargets as $index => $target) {
                    $from = (float) ($target['sales_from'] ?? 0);
                    $to = (float) ($target['sales_to'] ?? 0);

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
}
