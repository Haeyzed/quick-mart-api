<?php

declare(strict_types=1);

namespace App\Http\Requests\SalaryComponents;

use App\Http\Requests\BaseRequest;

class StoreSalaryComponentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:earning,deduction'],
            'is_taxable' => ['nullable', 'boolean'],
            'calculation_type' => ['nullable', 'string', 'in:fixed,percentage,formula'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
