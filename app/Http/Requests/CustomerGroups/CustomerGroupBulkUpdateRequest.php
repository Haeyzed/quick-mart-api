<?php

declare(strict_types=1);

namespace App\Http\Requests\CustomerGroups;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for bulk customer group activate/deactivate validation.
 *
 * Validates that ids array contains valid customer group IDs.
 */
class CustomerGroupBulkUpdateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('customer_groups', 'id')],
        ];
    }
}
