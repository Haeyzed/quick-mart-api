<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;

class CustomerReportExportRequest extends BaseRequest
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
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ];

        return array_merge($base, $this->exportRules());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('customer_id')) {
            $this->merge(['customer_id' => (int)$this->customer_id]);
        }
    }
}
