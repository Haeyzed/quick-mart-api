<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Form request for audit index/listing query parameters.
 *
 * Validates per_page, page, search, event, auditable_type.
 */
class AuditIndexRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'string', 'in:created,updated,deleted,restored'],
            'auditable_type' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'user' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('per_page')) {
            $this->merge(['per_page' => (int)$this->per_page]);
        }
        if ($this->has('page')) {
            $this->merge(['page' => (int)$this->page]);
        }
    }
}
