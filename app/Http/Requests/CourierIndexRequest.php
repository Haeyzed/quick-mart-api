<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * CourierIndexRequest
 *
 * Validates query parameters for courier index endpoint.
 */
class CourierIndexRequest extends FormRequest
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
             * Number of items to return per page.
             *
             * @var int|null @per_page
             * @example 10
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            /**
             * The page number for pagination.
             *
             * @var int|null @page
             * @example 1
             */
            'page' => ['nullable', 'integer', 'min:1'],
            /**
             * Filter couriers by active status.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Search term to filter couriers by name, phone number, or address.
             *
             * @var string|null @search
             * @example dhl
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}

