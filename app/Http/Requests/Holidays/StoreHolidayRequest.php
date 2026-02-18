<?php

declare(strict_types=1);

namespace App\Http\Requests\Holidays;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'note' => ['nullable', 'string', 'max:500'],
            'is_approved' => ['nullable', 'boolean'],
            'recurring' => ['nullable', 'boolean'],
            'region' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_approved')) {
            $this->merge(['is_approved' => filter_var($this->is_approved, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('recurring')) {
            $this->merge(['recurring' => filter_var($this->recurring, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->filled('from_date')) {
            $this->merge(['from_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->from_date)))]);
        }
        if ($this->filled('to_date')) {
            $this->merge(['to_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->to_date)))]);
        }
    }
}
