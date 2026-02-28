<?php

declare(strict_types=1);

namespace App\Http\Requests\SalaryStructures;

use App\Http\Requests\BaseRequest;

class StoreSalaryStructureRequest extends BaseRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'pay_frequency' => ['nullable', 'string', 'in:monthly,weekly,biweekly'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.salary_component_id' => ['required_with:items', 'integer', 'exists:salary_components,id'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
