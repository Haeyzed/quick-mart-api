<?php

declare(strict_types=1);

namespace App\Http\Requests\EmployeeDocuments;

use App\Http\Requests\BaseRequest;

/**
 * Class StoreEmployeeDocumentRequest
 *
 * Handles validation and authorization for creating a new employee document.
 */
class StoreEmployeeDocumentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,webp', 'max:5120'],
            'file_path' => ['nullable', 'string', 'max:500'],
            'file_url' => ['nullable', 'string', 'url', 'max:500'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
