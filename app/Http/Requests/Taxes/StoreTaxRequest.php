<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreTaxRequest
 *
 * Handles validation and authorization for creating a new tax.
 */
class StoreTaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * You can use it to sanitize or format inputs (e.g., casting string booleans to actual booleans).
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
        return [
            /**
             * The unique name of the tax.
             *
             * @example VAT 10%
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->withoutTrashed(),
            ],

            /**
             * The tax rate as a percentage (0–100).
             *
             * @example 10
             */
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],

            /**
             * Optional WooCommerce tax ID for sync.
             *
             * @example 1
             */
            'woocommerce_tax_id' => [
                'nullable',
                'integer',
                Rule::unique('taxes', 'woocommerce_tax_id')->withoutTrashed(),
            ],

            /**
             * Indicates whether the tax should be active upon creation.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
