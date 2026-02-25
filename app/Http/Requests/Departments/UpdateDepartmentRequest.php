<?php

declare(strict_types=1);

namespace App\Http\Requests\Departments;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateDepartmentRequest
 *
 * Handles validation and authorization for updating an existing department.
 */
class UpdateDepartmentRequest extends FormRequest
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
        /** @var Department|null $department */
        $department = $this->route('department');

        return [
            /**
             * The unique name of the department.
             *
             * @example Information Technology
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($department)->withoutTrashed(),
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
