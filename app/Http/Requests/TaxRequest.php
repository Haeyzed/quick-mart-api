<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * TaxRequest
 *
 * Validates incoming data for both creating and updating taxes.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class TaxRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        $taxId = $this->route('tax');

        return [
            /**
             * Tax name. Must be unique across all taxes.
             *
             * @var string @name
             * @example VAT
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->ignore($taxId),
            ],
            /**
             * Tax rate as a percentage (0-100).
             *
             * @var float @rate
             * @example 15.5
             */
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            /**
             * Whether the tax is active and visible.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * WooCommerce tax ID for sync purposes. Must be unique.
             *
             * @var int|null @woocommerce_tax_id
             * @example 123
             */
            'woocommerce_tax_id' => [
                'nullable',
                'integer',
                Rule::unique('taxes', 'woocommerce_tax_id')->ignore($taxId),
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
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'woocommerce_tax_id' => $this->woocommerce_tax_id ?: null,
        ]);
    }

}

