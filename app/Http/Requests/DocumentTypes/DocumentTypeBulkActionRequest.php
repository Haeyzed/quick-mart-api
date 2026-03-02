<?php

declare(strict_types=1);

namespace App\Http\Requests\DocumentTypes;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class DocumentTypeBulkActionRequest
 *
 * Handles validation and authorization for bulk actions on document types.
 */
class DocumentTypeBulkActionRequest extends BaseRequest
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
             * Array of document type IDs.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * Each ID must exist in document_types.
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('document_types', 'id')->withoutTrashed()],
        ];
    }
}
