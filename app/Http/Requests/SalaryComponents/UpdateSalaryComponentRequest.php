<?php

declare(strict_types=1);

namespace App\Http\Requests\SalaryComponents;

use App\Http\Requests\BaseRequest;

class UpdateSalaryComponentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_taxable')) {
            $this->merge(['is_taxable' => filter_var($this->is_taxable, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'in:earning,deduction'],
            'is_taxable' => ['nullable', 'boolean'],
            'calculation_type' => ['nullable', 'string', 'in:fixed,percentage,formula'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
