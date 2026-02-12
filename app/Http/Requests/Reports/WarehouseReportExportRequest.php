<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;

class WarehouseReportExportRequest extends BaseRequest
{
    use HasReportExportRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'type' => ['nullable', 'string', 'in:sales,purchases,both'],
        ], $this->exportRules());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('warehouse_id')) {
            $this->merge(['warehouse_id' => (int) $this->warehouse_id]);
        }
    }
}
