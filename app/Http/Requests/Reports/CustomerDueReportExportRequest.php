<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for customer due report export.
 */
class CustomerDueReportExportRequest extends BaseRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'format' => ['required', 'string', Rule::in(['excel', 'pdf'])],
            'method' => ['required', 'string', Rule::in(['download', 'email'])],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['string'],
            'user_id' => ['required_if:method,email', 'integer', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('customer_id')) {
            $this->merge(['customer_id' => (int)$this->customer_id]);
        }
        if ($this->has('user_id')) {
            $this->merge(['user_id' => (int)$this->user_id]);
        }
    }
}
