<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'code')->withoutTrashed(),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->withoutTrashed(),
            ],
            'base_unit' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->withoutTrashed(),
            ],
            'operator' => ['nullable', 'string', 'required_with:base_unit', 'in:*,/,+,-'],
            'operation_value' => ['nullable', 'numeric', 'required_with:base_unit', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
