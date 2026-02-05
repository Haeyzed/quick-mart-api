<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * UnitIndexRequest
 *
 * Validates query parameters for unit index endpoint.
 */
class UnitIndexRequest extends BaseRequest
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
             * @var int|null $per_page
             * @example 10
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            /**
             * Page number for pagination.
             *
             * @var int|null $page
             * @example 1
             */
            'page' => ['nullable', 'integer', 'min:1'],
            /**
             * Filter units by active status.
             *
             * @var string|null $status
             * @example active
             */
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            /**
             * Search term to filter units by code or name.
             *
             * @var string|null $search
             * @example kg
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => $this->per_page ? (int) $this->per_page : null,
            'page' => $this->page ? (int) $this->page : null,
        ]);
    }
}

