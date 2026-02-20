<?php

declare(strict_types=1);

namespace App\Http\Requests\CustomerGroups;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateCustomerGroupRequest
 *
 * Handles validation and authorization for updating an existing customer group.
 */
class UpdateCustomerGroupRequest extends FormRequest
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
        $customerGroup = $this->route('customer_group');

        return [
            /**
             * The name of the customer group. Must be unique excluding the currently updating group.
             *
             * @example Retail
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('customer_groups', 'name')->ignore($customerGroup)->withoutTrashed(),
            ],

            /**
             * Discount percentage for the group (0–100).
             *
             * @example 15
             */
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            /**
             * Indicates whether the customer group is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
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
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
