<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * GiftCardIndexRequest
 *
 * Validates query parameters for gift card index endpoint.
 */
class GiftCardIndexRequest extends FormRequest
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
             * Filter gift cards by active status.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Filter gift cards by customer ID.
             *
             * @var int|null @customer_id
             * @example 1
             */
            'customer_id' => ['nullable', 'integer'],
            /**
             * Filter gift cards by user ID.
             *
             * @var int|null @user_id
             * @example 1
             */
            'user_id' => ['nullable', 'integer'],
            /**
             * Search term to filter gift cards by card number.
             *
             * @var string|null @search
             * @example 1234
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}

