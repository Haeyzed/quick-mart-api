<?php

declare(strict_types=1);

namespace App\Http\Requests\Incomes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_no' => ['nullable', 'string', 'max:255'],
            'income_category_id' => ['required', 'integer', Rule::exists('income_categories', 'id')],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'cash_register_id' => ['nullable', 'integer', Rule::exists('cash_registers', 'id')],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('created_at')) {
            $c = str_replace('/', '-', $this->created_at);
            $this->merge(['created_at' => strlen($c) <= 10 ? date('Y-m-d H:i:s', strtotime($c)) : date('Y-m-d H:i:s', strtotime($c))]);
        }
    }
}
