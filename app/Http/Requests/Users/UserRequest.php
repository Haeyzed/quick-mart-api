<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * UserRequest
 *
 * Validates incoming data for both creating and updating users.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class UserRequest extends BaseRequest
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
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            /**
             * The user's full name.
             *
             * @var string $name
             * @example John Doe
             */
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /**
             * The user's username. Must be unique if provided.
             *
             * @var string|null $username
             * @example john_doe
             */
            'username' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ],

            /**
             * The user's email address. Must be unique across all users.
             *
             * @var string $email
             * @example john.doe@example.com
             */
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            /**
             * The user's password. Required on create, optional on update.
             * Must be at least 8 characters long.
             *
             * @var string|null $password
             * @example SecurePassword123!
             */
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            /**
             * The user's phone number.
             *
             * @var string|null $phone
             * @example +1234567890
             */
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            /**
             * The user's company name.
             *
             * @var string|null $company_name
             * @example Acme Corporation
             */
            'company_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            /**
             * The biller ID associated with the user.
             *
             * @var int|null $biller_id
             * @example 1
             */
            'biller_id' => [
                'nullable',
                'integer',
                'exists:billers,id',
            ],

            /**
             * The warehouse ID associated with the user.
             *
             * @var int|null $warehouse_id
             * @example 1
             */
            'warehouse_id' => [
                'nullable',
                'integer',
                'exists:warehouses,id',
            ],

            /**
             * Whether the user is active.
             *
             * @var bool|null $is_active
             * @example true
             */
            'is_active' => [
                'nullable',
                'boolean',
            ],

            /**
             * Array of role IDs to assign to the user.
             *
             * @var array<int>|null $roles
             * @example [1, 2]
             */
            'roles' => [
                'nullable',
                'array',
            ],

            /**
             * Array of role IDs to assign to the user.
             *
             * @var array<int>|null $roles .*
             * @example 1
             */
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],

            /**
             * Array of permission IDs to assign directly to the user.
             *
             * @var array<int>|null $permissions
             * @example [4, 5, 6]
             */
            'permissions' => [
                'nullable',
                'array',
            ],

            /**
             * Array of permission IDs to assign directly to the user.
             *
             * @var array<int>|null $permissions .*
             * @example 4
             */
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ];
    }
}
