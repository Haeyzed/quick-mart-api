<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;

/**
 * Form request for product quantity alert report query parameters.
 */
class ProductQtyAlertRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

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
