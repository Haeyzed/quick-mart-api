<?php

declare(strict_types=1);

namespace App\Http\Requests\Currencies;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateCurrencyRequest
 *
 * Handles validation and authorization for updating an existing currency.
 */
class UpdateCurrencyRequest extends FormRequest
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
        /** @var Currency|null $currency */
        $currency = $this->route('currency');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('currencies', 'code')->ignore($currency)],
            'symbol' => ['sometimes', 'required', 'string', 'max:255'],
            'country_id' => ['sometimes', 'required', 'integer', Rule::exists('countries', 'id')],
            'precision' => ['nullable', 'integer'],
            'symbol_native' => ['nullable', 'string', 'max:255'],
            'symbol_first' => ['nullable', 'boolean'],
            'decimal_mark' => ['nullable', 'string', 'max:1'],
            'thousands_separator' => ['nullable', 'string', 'max:1'],
        ];
    }
}
