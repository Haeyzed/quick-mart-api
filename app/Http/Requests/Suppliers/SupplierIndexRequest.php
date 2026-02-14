<?php

declare(strict_types=1);

namespace App\Http\Requests\Suppliers;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SupplierIndexRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

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
