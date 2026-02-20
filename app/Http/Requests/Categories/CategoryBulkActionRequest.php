<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class CategoryBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions (like deletion or status updates) on multiple categories.
 */
class CategoryBulkActionRequest extends FormRequest
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
            /**
             * An array of valid category IDs to perform the bulk action on.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single category ID ensuring it exists in the database (excluding trashed).
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('categories', 'id')->withoutTrashed()],
        ];
    }
}
