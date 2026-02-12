<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Reports\Concerns\HasReportExportRules;

class UserReportExportRequest extends BaseRequest
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
            'filter_user_id' => ['required', 'integer', 'exists:users,id'],
        ], $this->exportRules());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('filter_user_id')) {
            $this->merge(['filter_user_id' => (int) $this->filter_user_id]);
        }
    }
}
