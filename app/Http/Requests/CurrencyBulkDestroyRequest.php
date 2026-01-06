<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CurrencyBulkDestroyRequest
 *
 * Validates bulk delete request for currencies.
 */
class CurrencyBulkDestroyRequest extends FormRequest
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
        return [
            /**
             * Array of currency IDs to be deleted.
             *
             * @var array<int> @ids
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],
            /**
             * Each ID in the array must be an integer and exist in the currencies table.
             *
             * @var int @ids.*
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('currencies', 'id')],
        ];
    }
}

