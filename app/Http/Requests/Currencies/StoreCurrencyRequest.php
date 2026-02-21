<?php

declare(strict_types=1);

namespace App\Http\Requests\Currencies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreCurrencyRequest
 *
 * Handles validation and authorization for creating a new currency.
 */
class StoreCurrencyRequest extends FormRequest
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
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('symbol_first')) {
            $this->merge([
                'symbol_first' => filter_var($this->symbol_first, FILTER_VALIDATE_BOOLEAN),
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
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('currencies', 'code')],
            'symbol' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'precision' => ['nullable', 'integer'],
            'symbol_native' => ['nullable', 'string', 'max:255'],
            'symbol_first' => ['nullable', 'boolean'],
            'decimal_mark' => ['nullable', 'string', 'max:1'],
            'thousands_separator' => ['nullable', 'string', 'max:1'],
        ];
    }
}
