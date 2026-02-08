<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * UnitBulkUpdateRequest
 *
 * Validates bulk update request for units (activate/deactivate).
 */
class UnitBulkUpdateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Array of unit IDs to update.
             *
             * @var array<int> $ids
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],
            /**
             * Each ID in the ids array must be a valid unit ID.
             *
             * @var int $ids.*
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('units', 'id')->whereNull('deleted_at')],
        ];
    }
}

