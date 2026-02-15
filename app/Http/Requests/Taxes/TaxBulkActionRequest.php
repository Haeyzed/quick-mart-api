<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxBulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'required',
                'integer',
                Rule::exists('taxes', 'id')->withoutTrashed(),
            ],
        ];
    }
}
