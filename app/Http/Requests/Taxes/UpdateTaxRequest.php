<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use App\Models\Tax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxRequest extends FormRequest
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
        /** @var Tax|null $tax */
        $tax = $this->route('tax');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->ignore($tax)->withoutTrashed(),
            ],
            'rate' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'woocommerce_tax_id' => [
                'nullable',
                'integer',
                Rule::unique('taxes', 'woocommerce_tax_id')->ignore($tax)->withoutTrashed(),
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
