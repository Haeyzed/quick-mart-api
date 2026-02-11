<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for audit log export.
 */
class AuditLogExportRequest extends BaseRequest
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
            'ids' => ['sometimes', 'array'],
            'ids.*' => ['required', 'integer'],
            'format' => ['required', 'string', Rule::in(['excel', 'pdf'])],
            'method' => ['required', 'string', Rule::in(['download', 'email'])],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['string'],
            'user_id' => ['required_if:method,email', 'integer', 'exists:users,id'],
        ];
    }
}
