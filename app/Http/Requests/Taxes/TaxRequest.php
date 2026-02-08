<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for tax create and update validation.
 *
 * Validates name, rate, woocommerce_tax_id, and is_active.
 * Unique rules scope to non-soft-deleted taxes only.
 */
class TaxRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True to allow (authorization handled by middleware/policy).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tax = $this->route('tax');
        $taxId = $tax?->id ?? $tax;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->ignore($taxId)->whereNull('deleted_at'),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'woocommerce_tax_id' => [
                'nullable',
                'integer',
                Rule::unique('taxes', 'woocommerce_tax_id')->ignore($taxId)->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tax name is required.',
            'name.unique' => 'A tax with this name already exists.',
            'rate.required' => 'Tax rate is required.',
            'rate.numeric' => 'Tax rate must be a number.',
            'rate.min' => 'Tax rate must be at least 0.',
            'rate.max' => 'Tax rate cannot exceed 100.',
            'woocommerce_tax_id.unique' => 'This WooCommerce ID is already assigned to another tax.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Normalizes is_active and woocommerce_tax_id when present.
     */
    protected function prepareForValidation(): void
    {
        $isActive = $this->has('is_active') && $this->is_active !== null
            ? filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $this->merge([
            'woocommerce_tax_id' => $this->woocommerce_tax_id ?: null,
            'is_active' => $isActive,
        ]);
    }
}
