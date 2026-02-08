<?php

declare(strict_types=1);

namespace App\Http\Requests\Warehouses;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for bulk warehouse delete validation.
 *
 * Validates that ids array contains valid warehouse IDs.
 */
class WarehouseBulkDestroyRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True to allow.
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
            'ids.*' => ['required', 'integer', Rule::exists('warehouses', 'id')],
        ];
    }
}
