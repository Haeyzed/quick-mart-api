<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DiscountPlanRequest
 *
 * Validates incoming data for both creating and updating discount plans.
 */
class DiscountPlanRequest extends FormRequest
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
        $discountPlanId = $this->route('discount_plan');

        return [
            /**
             * The discount plan name. Must be unique across all discount plans.
             *
             * @var string @name
             * @example VIP Plan
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('discount_plans', 'name')->ignore($discountPlanId),
            ],
            /**
             * Type of discount plan (generic or limited).
             *
             * @var string|null @type
             * @example limited
             */
            'type' => ['nullable', 'string', Rule::in(['generic', 'limited'])],
            /**
             * Whether the discount plan is active.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Array of customer IDs to assign to this discount plan.
             *
             * @var array<int>|null @customer_id
             * @example [1, 2, 3]
             */
            'customer_id' => ['nullable', 'array'],
            /**
             * Individual customer ID in the customer_id array.
             *
             * @var int @customer_id.*
             * @example 1
             */
            'customer_id.*' => ['required', 'integer', Rule::exists('customers', 'id')],
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

