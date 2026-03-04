<?php

declare(strict_types=1);

namespace App\Http\Requests\DocumentTypes;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreDocumentTypeRequest
 *
 * Handles validation and authorization for creating a new document type.
 */
class StoreDocumentTypeRequest extends BaseRequest
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
            /**
             * Display name of the document type.
             *
             * @example "National ID"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * Unique code for the document type.
             *
             * @example "national_id"
             */
            'code' => ['required', 'string', 'max:64', Rule::unique('document_types', 'code')->withoutTrashed()],

            /**
             * Whether documents of this type must have an expiry date.
             *
             * @example true
             */
            'requires_expiry' => ['nullable', 'boolean'],

            /**
             * Whether the document type is active and available for use.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('requires_expiry')) {
            $this->merge(['requires_expiry' => filter_var($this->requires_expiry, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
