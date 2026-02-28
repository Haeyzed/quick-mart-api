<?php

declare(strict_types=1);

namespace App\Http\Requests\EmployeeDocuments;

use App\Http\Requests\BaseRequest;

class UpdateEmployeeDocumentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['sometimes', 'required', 'integer', 'exists:employees,id'],
            'document_type_id' => ['sometimes', 'required', 'integer', 'exists:document_types,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'file_path' => ['nullable', 'string', 'max:500'],
            'file_url' => ['nullable', 'string', 'url', 'max:500'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
