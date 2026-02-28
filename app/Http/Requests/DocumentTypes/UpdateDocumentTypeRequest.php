<?php

declare(strict_types=1);

namespace App\Http\Requests\DocumentTypes;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentTypeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('requires_expiry')) {
            $this->merge(['requires_expiry' => filter_var($this->requires_expiry, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:64', Rule::unique('document_types', 'code')->ignore($this->route('document_type'))->withoutTrashed()],
            'requires_expiry' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
