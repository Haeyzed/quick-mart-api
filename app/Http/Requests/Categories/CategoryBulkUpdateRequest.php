<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * CategoryBulkUpdateRequest
 *
 * Validates bulk update request for categories.
 * This single request handles all bulk update operations (activate, deactivate, etc.)
 */
class CategoryBulkUpdateRequest extends BaseRequest
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
             * Array of category IDs to update.
             *
             * @var array<int> $ids
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],
            /**
             * Each ID in the ids array must be a valid category ID.
             *
             * @var int $ids.*
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('categories', 'id')],
            /**
             * The action to perform on the selected categories.
             * Allowed values: activate, deactivate, enable_featured, disable_featured, enable_sync, disable_sync.
             *
             * @var string $action
             * @example activate
             */
            'action' => ['required', 'string', 'in:activate,deactivate,enable_featured,disable_featured,enable_sync,disable_sync'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Please select at least one category.',
            'ids.array' => 'Category IDs must be provided as an array.',
            'ids.min' => 'Please select at least one category.',
            'ids.*.integer' => 'Each category ID must be a valid integer.',
            'ids.*.exists' => 'One or more selected categories do not exist.',
            'action.required' => 'An action must be specified.',
            'action.in' => 'The action must be one of: activate, deactivate, enable_featured, disable_featured, enable_sync, or disable_sync.',
        ];
    }
}
