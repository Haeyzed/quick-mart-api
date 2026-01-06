<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * CourierRequest
 *
 * Validates incoming data for both creating and updating couriers.
 */
class CourierRequest extends BaseRequest
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
             * The courier name.
             *
             * @var string @name
             * @example DHL Express
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Courier contact phone number.
             *
             * @var string|null @phone_number
             * @example +1234567890
             */
            'phone_number' => ['nullable', 'string', 'max:255'],
            /**
             * Courier address.
             *
             * @var string|null @address
             * @example 123 Main St, City, State 12345
             */
            'address' => ['nullable', 'string'],
            /**
             * Whether the courier is active.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
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
            'phone_number' => $this->phone_number ? trim($this->phone_number) : null,
            'address' => $this->address ? trim($this->address) : null,
        ]);
    }

}

