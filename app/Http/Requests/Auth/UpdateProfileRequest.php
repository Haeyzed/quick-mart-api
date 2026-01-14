<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * UpdateProfileRequest
 *
 * Validates incoming profile update data.
 */
class UpdateProfileRequest extends BaseRequest
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
        $user = $this->user();

        return [
            /**
             * User's name.
             *
             * @var string
             * @example John Doe
             */
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * User's email address. Must be unique if provided.
             *
             * @var string|null
             * @example john.doe@example.com
             */
            'email' => [
                'sometimes',
                'nullable',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],

            /**
             * User's phone number.
             *
             * @var string|null
             * @example +1234567890
             */
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],

            /**
             * User's company name.
             *
             * @var string|null
             * @example Acme Corporation
             */
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
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
            'phone' => $this->phone ? trim($this->phone) : null,
            'company_name' => $this->company_name ? trim($this->company_name) : null,
        ]);
    }
}

