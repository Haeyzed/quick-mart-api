<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CategoryIndexRequest
 *
 * Validates query parameters for category index endpoint.
 */
class CategoryIndexRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Number of items per page for pagination.
             *
             * @var int|null @per_page
             * @example 10
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            /**
             * Page number for pagination.
             *
             * @var int|null @page
             * @example 1
             */
            'page' => ['nullable', 'integer', 'min:1'],
            /**
             * Filter categories by active status.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Filter categories by featured status.
             *
             * @var bool|null @featured
             * @example false
             */
            'featured' => ['nullable', 'boolean'],
            /**
             * Filter categories by parent category ID.
             *
             * @var int|null @parent_id
             * @example 1
             */
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            /**
             * Search term to filter categories by name, description, or slug.
             *
             * @var string|null @search
             * @example electronics
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}

