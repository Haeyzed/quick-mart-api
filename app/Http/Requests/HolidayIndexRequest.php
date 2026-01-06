<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * HolidayIndexRequest
 *
 * Validates query parameters for holiday index endpoint.
 */
class HolidayIndexRequest extends FormRequest
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
             * Filter holidays by user ID.
             *
             * @var int|null @user_id
             * @example 1
             */
            'user_id' => ['nullable', 'integer'],
            /**
             * Filter holidays by approval status.
             *
             * @var bool|null @is_approved
             * @example true
             */
            'is_approved' => ['nullable', 'boolean'],
            /**
             * Search term to filter holidays by note.
             *
             * @var string|null @search
             * @example annual
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}

