<?php

declare(strict_types=1);

namespace App\Http\Requests\Billers;

use App\Models\Biller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateBillerRequest
 *
 * Handles validation and authorization for updating an existing biller record.
 */
class UpdateBillerRequest extends FormRequest
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
        /** @var Biller|null $biller */
        $biller = $this->route('biller');

        return [
            /**
             * The full name of the biller.
             *
             * @example John Doe
             */
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * The unique email address for the biller (excluding the current record).
             *
             * @example johndoe@example.com
             */
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('billers', 'email')->ignore($biller)->withoutTrashed()],

            /**
             * The phone number of the biller.
             *
             * @example +1234567890
             */
            'phone_number' => ['sometimes', 'required', 'string', 'max:255'],

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
             * The optional new image to replace the old one.
             */
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],

            /**
             * Determines if the biller is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
