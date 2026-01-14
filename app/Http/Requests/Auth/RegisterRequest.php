<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * RegisterRequest
 *
 * Validates incoming registration data for new user creation.
 */
class RegisterRequest extends BaseRequest
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
        return [
            /**
             * User's name. Must be unique across all users.
             *
             * @var string @name
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
             * User's email address. Must be unique if provided.
             *
             * @var string|null @email
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
             * User's phone number.
             *
             * @var string|null @phone_number
             * @example +1234567890
             */
            'phone_number' => ['nullable', 'string', 'max:255'],

            /**
             * Company name.
             *
             * @var string|null @company_name
             * @example Acme Corporation
             */
            'company_name' => ['nullable', 'string', 'max:255'],

            /**
             * User's password. Must be confirmed.
             *
             * @var string @password
             * @example password123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * User's role ID.
             *
             * @var int @role_id
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
             * @var int|null @biller_id
             * @example 1
             */
            'biller_id' => ['nullable', 'integer', Rule::exists('billers', 'id')],

            /**
             * Warehouse ID (optional).
             *
             * @var int|null @warehouse_id
             * @example 1
             */
            'warehouse_id' => ['nullable', 'integer', Rule::exists('warehouses', 'id')],

            /**
             * Customer group ID (required if role_id is 5 - customer).
             *
             * @var int|null @customer_group_id
             * @example 1
             */
            'customer_group_id' => [
                'nullable',
                'integer',
                Rule::exists('customer_groups', 'id'),
            ],

            /**
             * Customer name (required if role_id is 5 - customer).
             *
             * @var string|null @customer_name
             * @example John Doe
             */
            'customer_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $this->role_id == 5),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'email' => $this->email ? trim($this->email) : null,
            'phone_number' => $this->phone_number ? trim($this->phone_number) : null,
            'company_name' => $this->company_name ? trim($this->company_name) : null,
            'customer_name' => $this->customer_name ? trim($this->customer_name) : null,
        ]);
    }
}

