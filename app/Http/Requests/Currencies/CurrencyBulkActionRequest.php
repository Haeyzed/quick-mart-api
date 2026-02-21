<?php

declare(strict_types=1);

namespace App\Http\Requests\Currencies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class CurrencyBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions on multiple currencies.
 */
class CurrencyBulkActionRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('currencies', 'id')],
        ];
    }
}
