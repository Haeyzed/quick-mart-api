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
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * You can use it to sanitize or format inputs (e.g., casting string booleans to actual booleans).
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
             * Indicates whether the department should be active upon creation.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
