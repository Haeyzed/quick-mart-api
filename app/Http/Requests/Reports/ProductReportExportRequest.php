<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;

class ProductReportExportRequest extends BaseRequest
{
    use HasReportExportRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ], $this->exportRules());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('warehouse_id')) {
            $this->merge(['warehouse_id' => (int) $this->warehouse_id]);
        }
        if ($this->has('category_id')) {
            $this->merge(['category_id' => (int) $this->category_id]);
        }
    }
}
