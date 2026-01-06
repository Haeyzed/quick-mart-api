<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * WarehouseRequest
 *
 * Validates incoming data for both creating and updating warehouses.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class WarehouseRequest extends BaseRequest
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
        $warehouseId = $this->route('warehouse');

        return [
            /**
             * The warehouse name. Must be unique across all warehouses.
             *
             * @var string @name
             * @example Main Warehouse
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name')->ignore($warehouseId),
            ],
            /**
             * Warehouse contact phone number.
             *
             * @var string|null @phone
             * @example +1234567890
             */
            'phone' => ['nullable', 'string', 'max:255'],
            /**
             * Warehouse contact email address.
             *
             * @var string|null @email
             * @example warehouse@example.com
             */
            'email' => ['nullable', 'email', 'max:255'],
            /**
             * Warehouse physical address.
             *
             * @var string|null @address
             * @example 123 Main St, City, State 12345
             */
            'address' => ['nullable', 'string'],
            /**
             * Whether the warehouse is active.
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
            'phone' => $this->phone ? trim($this->phone) : null,
            'email' => $this->email ? trim($this->email) : null,
            'address' => $this->address ? trim($this->address) : null,
        ]);
    }

}

