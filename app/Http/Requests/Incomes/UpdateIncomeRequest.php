<?php

declare(strict_types=1);

namespace App\Http\Requests\Incomes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'reference_no' => ['sometimes', 'nullable', 'string', 'max:255'],
            'income_category_id' => ['sometimes', 'required', 'integer', Rule::exists('income_categories', 'id')],
            'warehouse_id' => ['sometimes', 'required', 'integer', Rule::exists('warehouses', 'id')],
            'account_id' => ['sometimes', 'required', 'integer', Rule::exists('accounts', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'cash_register_id' => ['nullable', 'integer', Rule::exists('cash_registers', 'id')],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('created_at')) {
            $createdAt = str_replace('/', '-', $this->created_at);
            $this->merge([
                'created_at' => strlen($createdAt) <= 10
                    ? date('Y-m-d H:i:s', strtotime($createdAt))
                    : date('Y-m-d H:i:s', strtotime($createdAt)),
            ]);
        }
    }
}
