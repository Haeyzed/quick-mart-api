<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for tax index/listing query parameters.
 *
 * Validates per_page, page, status (active/inactive), and search term.
 */
class TaxIndexRequest extends BaseRequest
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
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Cast per_page and page to integers before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('per_page')) {
            $this->merge(['per_page' => (int)$this->per_page]);
        }
        if ($this->has('page')) {
            $this->merge(['page' => (int)$this->page]);
        }
    }
}
