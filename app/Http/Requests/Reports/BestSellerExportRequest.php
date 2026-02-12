<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;

class BestSellerExportRequest extends BaseRequest
{
    use HasReportExportRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge([
            'months' => ['nullable', 'integer', 'min:1', 'max:24'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
        ], $this->exportRules());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('warehouse_id')) {
            $this->merge(['warehouse_id' => (int) $this->warehouse_id]);
        }
    }
}
