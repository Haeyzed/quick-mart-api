<?php

declare(strict_types=1);

namespace App\Http\Requests\Billers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreBillerRequest
 *
 * Handles validation and authorization for creating a new biller record.
 */
class StoreBillerRequest extends FormRequest
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
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
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
             * The full name of the biller.
             *
             * @example John Doe
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The unique email address for the biller.
             *
             * @example johndoe@example.com
             */
            'email' => ['required', 'email', 'max:255', Rule::unique('billers', 'email')->withoutTrashed()],

            /**
             * The phone number of the biller.
             *
             * @example +1234567890
             */
            'phone_number' => ['required', 'string', 'max:255'],

            /**
             * The optional associated company name.
             *
             * @example ACME Corp
             */
            'company_name' => ['nullable', 'string', 'max:255'],

            /**
             * The optional VAT or Tax Identification Number.
             *
             * @example VAT-987654321
             */
            'vat_number' => ['nullable', 'string', 'max:50'],

            /**
             * The optional street address.
             *
             * @example 123 Main Street
             */
            'address' => ['nullable', 'string', 'max:255'],

            /**
             * The associated country ID.
             *
             * @example 1
             */
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],

            /**
             * The associated state ID.
             *
             * @example 12
             */
            'state_id' => ['nullable', 'integer', 'exists:states,id'],

            /**
             * The associated city ID.
             *
             * @example 45
             */
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],

            /**
             * The optional postal code.
             *
             * @example 90001
             */
            'postal_code' => ['nullable', 'string', 'max:20'],

            /**
             * The optional image or avatar for the biller.
             */
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],

            /**
             * Determines if the biller is active upon creation.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
