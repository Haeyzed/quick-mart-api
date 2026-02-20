<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Models\Role;
use Illuminate\Validation\Rule;

/**
 * Class RegisterRequest
 *
 * Handles validation and authorization for new user registration.
 */
class RegisterRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * User's name. Must be unique across all users.
             *
             * @example John Doe
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],

            /**
             * User's username. Must be unique if provided.
             *
             * @example john_doe
             */
            'username' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users', 'username')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],

            /**
             * User's email address. Must be unique if provided.
             *
             * @example john.doe@example.com
             */
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],

            /**
             * User's avatar image file.
             *
             * @example avatar.jpg
             */
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],

            /**
             * User's phone number.
             *
             * @example +1234567890
             */
            'phone' => ['nullable', 'string', 'max:255'],

            /**
             * Company name.
             *
             * @example Acme Corporation
             */
            'company_name' => ['nullable', 'string', 'max:255'],

            /**
             * User's password. Must be confirmed.
             *
             * @example password123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * User's role ID.
             *
             * @example 1
             */
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],

            /**
             * Biller ID (optional).
             *
             * @example 1
             */
            'biller_id' => ['nullable', 'integer', Rule::exists('billers', 'id')],

            /**
             * Warehouse ID (optional).
             *
             * @example 1
             */
            'warehouse_id' => ['nullable', 'integer', Rule::exists('warehouses', 'id')],

            /**
             * Customer group ID (required if role_id is 5 - customer).
             *
             * @example 1
             */
            'customer_group_id' => [
                'nullable',
                'integer',
                Rule::exists('customer_groups', 'id'),
            ],

            /**
             * Customer name (required when Customer role is selected).
             *
             * @example John Doe
             */
            'customer_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () {
                    $customerRole = Role::query()->where('name', 'Customer')->where('is_active', true)->first();

                    return $customerRole && (int) $this->role_id === (int) $customerRole->id;
                }),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * You can use it to sanitize or format inputs (e.g., trimming string fields).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'username' => $this->username ? trim($this->username) : null,
            'email' => $this->email ? trim($this->email) : null,
            'phone' => $this->phone ? trim($this->phone) : null,
            'company_name' => $this->company_name ? trim($this->company_name) : null,
            'customer_name' => $this->customer_name ? trim($this->customer_name) : null,
        ]);
    }
}
