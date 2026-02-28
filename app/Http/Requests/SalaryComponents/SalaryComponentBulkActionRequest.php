<?php

declare(strict_types=1);

namespace App\Http\Requests\SalaryComponents;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SalaryComponentBulkActionRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('salary_components', 'id')->withoutTrashed()],
        ];
    }
}
