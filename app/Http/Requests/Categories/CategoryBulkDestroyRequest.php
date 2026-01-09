<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Http\Requests\BaseRequest;

/**
 * CategoryBulkDestroyRequest
 *
 * Validates bulk delete request for categories.
 */
class CategoryBulkDestroyRequest extends BaseRequest
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
             * Array of category IDs to delete.
             *
             * @var array<int> @ids
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],
            /**
             * Each ID in the ids array must be a valid category ID.
             *
             * @var int @ids.*
             * @example 1
             */
            'ids.*' => ['required', 'integer', 'exists:categories,id'],
        ];
    }
}

