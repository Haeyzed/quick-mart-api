<?php

declare(strict_types=1);

namespace App\Http\Requests\Permissions;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class PermissionBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions on permission records.
 */
class PermissionBulkActionRequest extends BaseRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            /**
             * An array of valid permission IDs.
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single permission ID ensuring it exists in the database.
             * @example 1
             */
            'ids.*' => [
                'required',
                'integer',
                Rule::exists('permissions', 'id'),
            ],
        ];
    }
}
