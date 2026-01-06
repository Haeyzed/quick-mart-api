<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * VariantIndexRequest
 *
 * Validates query parameters for variant index endpoint.
 */
class VariantIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
             * Search term to filter variants by name.
             *
             * @var string|null @search
             * @example size
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}

