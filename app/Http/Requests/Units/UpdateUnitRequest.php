<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
        /** @var Unit|null $unit */
        $unit = $this->route('unit');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'code')->ignore($unit)->withoutTrashed(),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($unit)->withoutTrashed(),
            ],
            'base_unit' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->ignore($unit)->withoutTrashed(),
            ],
            'operator' => ['nullable', 'string', 'in:*,/,+,-'],
            'operation_value' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
