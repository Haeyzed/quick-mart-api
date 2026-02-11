<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;

/**
 * Form request for warehouse report query parameters.
 */
class WarehouseReportRequest extends BaseRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'type' => ['nullable', 'string', 'in:sales,purchases,both'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('warehouse_id')) {
            $this->merge(['warehouse_id' => (int) $this->warehouse_id]);
        }
        if ($this->has('per_page')) {
            $this->merge(['per_page' => (int) $this->per_page]);
        }
        if ($this->has('page')) {
            $this->merge(['page' => (int) $this->page]);
        }
    }
}
