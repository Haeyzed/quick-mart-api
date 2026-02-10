<?php

declare(strict_types=1);

namespace App\Http\Requests\SaleAgents;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SaleAgentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('sale_agent')?->id ?? $this->route('employee')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employeeId)->where('is_active', true),
            ],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,id'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'staff_id' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'sale_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sales_target' => ['nullable', 'array'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
