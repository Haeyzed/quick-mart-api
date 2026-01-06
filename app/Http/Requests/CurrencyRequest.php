<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CurrencyRequest
 *
 * Validates incoming data for both creating and updating currencies.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class CurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        $currencyId = $this->route('currency');

        return [
            /**
             * The currency name. Must be unique across all currencies.
             *
             * @var string @name
             * @example US Dollar
             */
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            /**
             * The currency code (ISO 4217). Must be unique across all currencies.
             *
             * @var string @code
             * @example USD
             */
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('currencies', 'code')->ignore($currencyId),
            ],
            /**
             * The currency symbol.
             *
             * @var string|null @symbol
             * @example $
             */
            'symbol' => ['nullable', 'string', 'max:10'],
            /**
             * Exchange rate relative to base currency.
             *
             * @var float @exchange_rate
             * @example 1.0
             */
            'exchange_rate' => ['required', 'numeric', 'min:0'],
            /**
             * Whether the currency is active.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
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
            'name' => $this->name ? trim($this->name) : null,
            'code' => $this->code ? strtoupper(trim($this->code)) : null,
            'symbol' => $this->symbol ? trim($this->symbol) : null,
        ]);
    }

}

