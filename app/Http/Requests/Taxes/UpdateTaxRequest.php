<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use App\Models\Tax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateTaxRequest
 *
 * Handles validation and authorization for updating an existing tax.
 */
class UpdateTaxRequest extends FormRequest
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
     * Useful for casting types or manipulating the request payload before validation.
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
        /** @var Tax|null $tax */
        $tax = $this->route('tax');

        return [
            /**
             * The name of the tax. Must be unique excluding the currently updating tax.
             *
             * @example VAT 10%
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->ignore($tax)->withoutTrashed(),
            ],

            /**
             * The tax rate as a percentage (0–100).
             *
             * @example 10
             */
            'rate' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],

            /**
             * Optional WooCommerce tax ID. Must be unique excluding the current tax.
             *
             * @example 1
             */
            'woocommerce_tax_id' => [
                'nullable',
                'integer',
                Rule::unique('taxes', 'woocommerce_tax_id')->ignore($tax)->withoutTrashed(),
            ],

            /**
             * Indicates whether the tax is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
