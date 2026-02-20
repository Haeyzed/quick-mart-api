<?php

declare(strict_types=1);

namespace App\Http\Requests\Warehouses;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateWarehouseRequest
 *
 * Handles validation and authorization for updating an existing warehouse.
 */
class UpdateWarehouseRequest extends FormRequest
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
     * Useful for casting types or manipulating the request payload before validation.
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
        /** @var Warehouse|null $warehouse */
        $warehouse = $this->route('warehouse');

        return [
            /**
             * The name of the warehouse. Must be unique excluding the currently updating warehouse.
             *
             * @example Main Store
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name')->ignore($warehouse)->withoutTrashed(),
            ],

            /**
             * Contact phone number for the warehouse.
             *
             * @example +1234567890
             */
            'phone' => ['sometimes', 'required', 'string', 'max:255'],

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
            'address' => ['sometimes', 'required', 'string'],

            /**
             * Indicates whether the warehouse is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
