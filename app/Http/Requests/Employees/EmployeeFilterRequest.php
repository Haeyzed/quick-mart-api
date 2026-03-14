<?php

declare(strict_types=1);

namespace App\Http\Requests\Employees;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Arr;

class EmployeeFilterRequest extends BaseRequest
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
     * Intercepts comma-separated strings and converts them to arrays.
     */
    protected function prepareForValidation(): void
    {
        $arrayFields = [
            'user_id', 'department_id', 'employment_type_id', 'designation_id',
            'reporting_manager_id', 'country_id', 'state_id', 'city_id',
            'warehouse_id', 'shift_id', 'is_active',
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);

                if (is_string($value)) {
                    // Explode, trim, and filter out strictly empty strings (preserves '0')
                    $this->merge([
                        $field => array_values(array_filter(
                            explode(',', $value),
                            fn ($val) => trim((string) $val) !== ''
                        )),
                    ]);
                } elseif (! is_array($value)) {
                    // Ensure single values are wrapped in an array
                    $this->merge([
                        $field => Arr::wrap($value),
                    ]);
                }
            }
        }
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
             * Search term to filter employees by name, email, phone, or staff ID.
             *
             * @example "Jane Doe"
             */
            'search' => ['nullable', 'string'],

            /**
             * Filter by active status. Accepts comma-separated booleans.
             *
             * @example "1,0"
             */
            'is_active' => ['nullable', 'array'],
            'is_active.*' => ['boolean'],

            /**
             * Filter to retrieve only sales agents. Accepts comma-separated booleans.
             *
             * @example "1,0"
             */
            'is_sale_agent' => ['nullable', 'array'],
            'is_sale_agent.*' => ['boolean'],

            /**
             * Filter by associated department IDs. Accepts comma-separated values.
             *
             * @example "2,5"
             */
            'department_id' => ['nullable', 'array'],
            'department_id.*' => ['integer', 'exists:departments,id'],

            /**
             * Filter by associated user IDs. Accepts comma-separated values.
             *
             * @example "10,12"
             */
            'user_id' => ['nullable', 'array'],
            'user_id.*' => ['integer', 'exists:users,id'],

            /**
             * Filter by employment type IDs (e.g., Full-time, Part-time). Accepts comma-separated values.
             *
             * @example "1,3"
             */
            'employment_type_id' => ['nullable', 'array'],
            'employment_type_id.*' => ['integer'],

            /**
             * Filter by designation IDs (e.g., Manager, Developer). Accepts comma-separated values.
             *
             * @example "4,7"
             */
            'designation_id' => ['nullable', 'array'],
            'designation_id.*' => ['integer'],

            /**
             * Filter by reporting manager's employee IDs. Accepts comma-separated values.
             *
             * @example "5,8"
             */
            'reporting_manager_id' => ['nullable', 'array'],
            'reporting_manager_id.*' => ['integer'],

            /**
             * Filter by country IDs. Accepts comma-separated values.
             *
             * @example "160"
             */
            'country_id' => ['nullable', 'array'],
            'country_id.*' => ['integer'],

            /**
             * Filter by state/province IDs. Accepts comma-separated values.
             *
             * @example "12,14"
             */
            'state_id' => ['nullable', 'array'],
            'state_id.*' => ['integer'],

            /**
             * Filter by city IDs. Accepts comma-separated values.
             *
             * @example "105,108"
             */
            'city_id' => ['nullable', 'array'],
            'city_id.*' => ['integer'],

            /**
             * Filter by associated warehouse IDs. Accepts comma-separated values.
             *
             * @example "1,2"
             */
            'warehouse_id' => ['nullable', 'array'],
            'warehouse_id.*' => ['integer'],

            /**
             * Filter by assigned shift IDs. Accepts comma-separated values.
             *
             * @example "2,3"
             */
            'shift_id' => ['nullable', 'array'],
            'shift_id.*' => ['integer'],

            /**
             * Filter by current employment status.
             *
             * @example "probation"
             */
            'employment_status' => ['nullable', 'string'],

            /**
             * Filter by a specific, exact employee code.
             *
             * @example "EMP00125"
             */
            'employee_code' => ['nullable', 'string'],

            /**
             * Filter employees starting from this date.
             *
             * @example "2024-01-01"
             */
            'start_date' => ['nullable', 'date'],

            /**
             * Filter employees up to this date.
             *
             * @example "2024-12-31"
             */
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],

            /**
             * Amount of items to return per page for pagination.
             *
             * @example 50
             */
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
