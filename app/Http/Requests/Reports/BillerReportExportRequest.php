<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;

class BillerReportExportRequest extends BaseRequest
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
            'biller_id' => ['required', 'integer', 'exists:billers,id'],
        ], $this->exportRules());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('biller_id')) {
            $this->merge(['biller_id' => (int) $this->biller_id]);
        }
    }
}
