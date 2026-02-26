<?php

declare(strict_types=1);

namespace App\Http\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreWarehouseRequest
 *
 * Handles validation and authorization for creating a new warehouse.
 */
class StoreWarehouseRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * You can use it to sanitize or format inputs (e.g., casting string booleans to actual booleans).
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
             * The unique name of the warehouse.
             *
             * @example Main Store
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name')->withoutTrashed(),
            ],

            /**
             * Contact phone number for the warehouse.
             *
             * @example +1234567890
             */
            'phone_number' => ['required', 'string', 'max:255'],

            /**
             * Contact email for the warehouse.
             *
             * @example warehouse@example.com
             */
            'email' => ['nullable', 'email', 'max:255'],

            /**
             * Physical address of the warehouse.
             *
             * @example 123 Storage Lane
             */
            'address' => ['required', 'string'],

            /**
             * Indicates whether the warehouse should be active upon creation.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
