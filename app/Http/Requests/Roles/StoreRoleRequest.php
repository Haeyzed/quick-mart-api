<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreRoleRequest
 *
 * Handles validation and authorization for creating a new role.
 */
class StoreRoleRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $guard = $this->input('guard_name', 'web');

        return [
            /**
             * The name of the role.
             * @example HR Manager
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', $guard)
            ],

            /**
             * The description of the role's purpose.
             * @example Manages all human resource activities.
             */
            'description' => ['nullable', 'string', 'max:500'],

            /**
             * The authentication guard the role belongs to.
             * @example web
             */
            'guard_name' => ['nullable', 'string', 'max:255'],

            /**
             * Determines if the role is active.
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * An array of permission IDs attached to this role.
             * @example [1, 2, 5]
             */
            'permissions' => ['nullable', 'array'],

            /**
             * Validate each permission exists.
             * @example 1
             */
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }
}
