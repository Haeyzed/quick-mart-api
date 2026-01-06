<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CustomerGroupRequest
 *
 * Validates incoming data for both creating and updating customer groups.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class CustomerGroupRequest extends FormRequest
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
        $customerGroupId = $this->route('customer_group');

        return [
            /**
             * The customer group name. Must be unique across all customer groups.
             *
             * @var string @name
             * @example VIP Customers
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customer_groups', 'name')->ignore($customerGroupId),
            ],
            /**
             * Discount percentage for this customer group.
             *
             * @var float|null @percentage
             * @example 10.5
             */
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            /**
             * Whether the customer group is active.
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
        ]);
    }

}

