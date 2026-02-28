<?php

declare(strict_types=1);

namespace App\Http\Requests\EmploymentTypes;

use App\Http\Requests\BaseRequest;
use App\Models\EmploymentType;
use Illuminate\Validation\Rule;

/**
 * Class UpdateEmploymentTypeRequest
 *
 * Handles validation and authorization for updating an employment type.
 */
class UpdateEmploymentTypeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var EmploymentType|null $employmentType */
        $employmentType = $this->route('employment_type');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('employment_types', 'name')->ignore($employmentType)->withoutTrashed(),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
