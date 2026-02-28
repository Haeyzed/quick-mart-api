<?php

declare(strict_types=1);

namespace App\Http\Requests\PayrollRuns;

use App\Http\Requests\BaseRequest;

class StorePayrollRunRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'status' => ['nullable', 'string', 'in:draft,processing,completed'],
        ];
    }
}
