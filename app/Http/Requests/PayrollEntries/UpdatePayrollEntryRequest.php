<?php

declare(strict_types=1);

namespace App\Http\Requests\PayrollEntries;

use App\Http\Requests\BaseRequest;

class UpdatePayrollEntryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gross_salary' => ['sometimes', 'numeric', 'min:0'],
            'total_deductions' => ['sometimes', 'numeric', 'min:0'],
            'net_salary' => ['sometimes', 'numeric'],
            'status' => ['nullable', 'string', 'in:draft,approved,paid'],
        ];
    }
}
