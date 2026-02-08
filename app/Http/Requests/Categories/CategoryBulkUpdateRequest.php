<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for bulk category activate/deactivate/featured/sync validation.
 *
 * Validates that ids array contains valid non-soft-deleted category IDs.
 * Action is implicit from the route (e.g. bulk-activate, bulk-deactivate).
 */
class CategoryBulkUpdateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True to allow.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
        ];
    }
}