<?php

declare(strict_types=1);

namespace App\Http\Requests\Departments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreDepartmentRequest
 *
 * Handles validation and authorization for creating a new department.
 */
class StoreDepartmentRequest extends FormRequest
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
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
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
        return [
            /**
             * The unique name of the department.
             *
             * @example Human Resources
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->withoutTrashed(),
            ],

            /**
             * Indicates whether the department is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
