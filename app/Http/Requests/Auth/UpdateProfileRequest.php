<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProfileRequest
 *
 * Handles validation and authorization for updating the authenticated user's profile.
 */
class UpdateProfileRequest extends BaseRequest
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
        $user = $this->user();

        return [
            /**
             * User's name.
             *
             * @example John Doe
             */
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * User's email address. Must be unique if provided.
             *
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
             * @example +1234567890
             */
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],

            /**
             * User's company name.
             *
             * @example Acme Corporation
             */
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
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
            'email' => $this->email ? trim($this->email) : null,
            'phone' => $this->phone ? trim($this->phone) : null,
            'company_name' => $this->company_name ? trim($this->company_name) : null,
        ]);
    }
}
