<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class RoleBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions on role records.
 */
class RoleBulkActionRequest extends BaseRequest
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
             * An array of valid role IDs.
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single role ID ensuring it exists in the database.
             * @example 1
             */
            'ids.*' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
        ];
    }
}
