<?php

declare(strict_types=1);

namespace App\Http\Requests\DocumentTypes;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DocumentTypeBulkActionRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('document_types', 'id')->withoutTrashed()],
        ];
    }
}
