<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;

class PaymentReportExportRequest extends BaseRequest
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
            'type' => ['nullable', 'string', 'in:sale,purchase,all'],
        ];

        return array_merge($base, $this->exportRules());
    }
}
