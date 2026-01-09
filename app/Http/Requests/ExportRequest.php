<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['sometimes', 'array'],
            'ids.*' => ['required', 'integer'],
            'format' => ['required', 'string', Rule::in(['excel', 'pdf'])],
            'method' => ['required', 'string', Rule::in(['download', 'email'])],
            'user_id' => ['required_if:method,email', 'integer', 'exists:users,id'],
        ];
    }
}

