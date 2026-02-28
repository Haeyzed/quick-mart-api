<?php

declare(strict_types=1);

namespace App\Http\Requests\EmploymentTypes;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class EmploymentTypeBulkActionRequest
 *
 * Handles validation for bulk actions on employment types.
 */
class EmploymentTypeBulkActionRequest extends BaseRequest
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
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('employment_types', 'id')->withoutTrashed()],
        ];
    }
}
