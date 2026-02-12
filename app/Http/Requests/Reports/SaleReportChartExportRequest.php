<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;
use Illuminate\Validation\Rule;

class SaleReportChartExportRequest extends BaseRequest
{
    use HasReportExportRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $base = [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'time_period' => ['nullable', 'string', Rule::in(['weekly', 'monthly'])],
            'product_list' => ['nullable', 'string'],
        ];

        return array_merge($base, $this->exportRules());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('warehouse_id')) {
            $this->merge(['warehouse_id' => (int) $this->warehouse_id]);
        }
    }
}
