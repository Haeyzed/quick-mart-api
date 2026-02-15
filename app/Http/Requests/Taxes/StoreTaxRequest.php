<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->withoutTrashed(),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'woocommerce_tax_id' => [
                'nullable',
                'integer',
                Rule::unique('taxes', 'woocommerce_tax_id')->withoutTrashed(),
            ],
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
