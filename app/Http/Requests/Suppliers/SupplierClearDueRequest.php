<?php

declare(strict_types=1);

namespace App\Http\Requests\Suppliers;

use App\Http\Requests\BaseRequest;

/**
 * Form request for supplier clear due (record payment against due purchases).
 */
class SupplierClearDueRequest extends BaseRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:500'],
            'cash_register_id' => ['nullable', 'integer', 'exists:cash_registers,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $this->merge(['amount' => (float)$this->amount]);
        }
    }
}
