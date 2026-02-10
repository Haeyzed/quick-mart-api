<?php

declare(strict_types=1);

namespace App\Http\Requests\CustomerGroups;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for bulk customer group delete validation.
 *
 * Validates that ids array contains valid customer group IDs.
 */
class CustomerGroupBulkDestroyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('customer_groups', 'id')],
        ];
    }
}
