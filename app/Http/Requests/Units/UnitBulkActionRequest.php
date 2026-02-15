<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitBulkActionRequest extends FormRequest
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
                Rule::exists('units', 'id')->withoutTrashed(),
            ],
        ];
    }
}
