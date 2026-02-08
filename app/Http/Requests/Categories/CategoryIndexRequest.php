<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for category index/listing query parameters.
 *
 * Validates per_page, page, status, featured_status, sync_status, parent_id, and search term.
 */
class CategoryIndexRequest extends BaseRequest
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
     * Get the validation rules for index query parameters.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'featured_status' => ['nullable', Rule::in(['featured', 'not featured'])],
            'sync_status' => ['nullable', Rule::in(['enabled', 'disabled'])],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Cast per_page and page to integers before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('per_page')) {
            $this->merge(['per_page' => (int) $this->per_page]);
        }
        if ($this->has('page')) {
            $this->merge(['page' => (int) $this->page]);
        }
    }
}