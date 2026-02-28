<?php

declare(strict_types=1);

namespace App\Http\Requests\WorkLocations;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreWorkLocationRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('work_locations', 'code')->withoutTrashed()],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
