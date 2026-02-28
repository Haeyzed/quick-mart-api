<?php

declare(strict_types=1);

namespace App\Http\Requests\PayrollRuns;

use App\Http\Requests\BaseRequest;

class UpdatePayrollRunRequest extends BaseRequest
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
            'month' => ['sometimes', 'required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'year' => ['sometimes', 'required', 'integer', 'min:2000', 'max:2100'],
            'status' => ['nullable', 'string', 'in:draft,processing,completed'],
        ];
    }
}
