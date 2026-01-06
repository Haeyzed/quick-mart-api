<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DiscountApplicableForEnum;
use App\Enums\DiscountTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DiscountRequest
 *
 * Validates incoming data for both creating and updating discounts.
 */
class DiscountRequest extends FormRequest
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
             * The discount name.
             *
             * @var string @name
             * @example Summer Sale
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * What the discount applies to (All or specific products).
             *
             * @var string @applicable_for
             * @example All
             */
            'applicable_for' => ['required', 'string', Rule::enum(DiscountApplicableForEnum::class)],
            /**
             * Array of product IDs (if applicable_for is Selected). Will be converted to comma-separated string.
             *
             * @var array<int>|null @product_list
             * @example [1, 2, 3]
             */
            'product_list' => [
                'nullable',
                'array',
                Rule::requiredIf(fn() => $this->applicable_for === DiscountApplicableForEnum::SELECTED->value),
            ],
            /**
             * Individual product ID in the product_list array.
             *
             * @var int @product_list.*
             * @example 1
             */
            'product_list.*' => ['required', 'integer', Rule::exists('products', 'id')],
            /**
             * Start date of discount validity.
             *
             * @var string @valid_from
             * @example 2024-01-01
             */
            'valid_from' => ['required', 'date'],
            /**
             * End date of discount validity.
             *
             * @var string @valid_till
             * @example 2024-12-31
             */
            'valid_till' => ['required', 'date', 'after_or_equal:valid_from'],
            /**
             * Discount type (percentage or fixed).
             *
             * @var string @type
             * @example percentage
             */
            'type' => ['required', 'string', Rule::enum(DiscountTypeEnum::class)],
            /**
             * Discount value (percentage or fixed amount).
             *
             * @var float @value
             * @example 10.5
             */
            'value' => ['required', 'numeric', 'min:0'],
            /**
             * Minimum quantity required for discount.
             *
             * @var float|null @minimum_qty
             * @example 1
             */
            'minimum_qty' => ['nullable', 'numeric', 'min:0'],
            /**
             * Maximum quantity allowed for discount.
             *
             * @var float|null @maximum_qty
             * @example 100
             */
            'maximum_qty' => ['nullable', 'numeric', 'min:0', 'gte:minimum_qty'],
            /**
             * Days of week when discount applies. Can be array or comma-separated string. Will be converted to comma-separated string.
             *
             * @var array<string>|string @days
             * @example ["Mon", "Tue", "Wed"] or "Mon,Tue,Wed"
             */
            'days' => ['required'],
            /**
             * Individual day in the days array.
             *
             * @var string @days.*
             * @example Mon
             */
            'days.*' => ['required', 'string', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
            /**
             * Whether the discount is active.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Array of discount plan IDs to assign this discount to.
             *
             * @var array<int>|null @discount_plan_id
             * @example [1, 2, 3]
             */
            'discount_plan_id' => ['nullable', 'array'],
            /**
             * Individual discount plan ID in the discount_plan_id array.
             *
             * @var int @discount_plan_id.*
             * @example 1
             */
            'discount_plan_id.*' => ['required', 'integer', Rule::exists('discount_plans', 'id')],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Normalize date formats (convert "/" to "-" and format to Y-m-d)
        $validFrom = $this->valid_from;
        $validTill = $this->valid_till;

        if ($validFrom) {
            $validFrom = str_replace('/', '-', $validFrom);
            $validFrom = date('Y-m-d', strtotime($validFrom));
        }

        if ($validTill) {
            $validTill = str_replace('/', '-', $validTill);
            $validTill = date('Y-m-d', strtotime($validTill));
        }

        // Clear product_list if applicable_for is All
        $productList = null;
        if ($this->applicable_for !== DiscountApplicableForEnum::ALL->value && isset($this->product_list)) {
            $productList = $this->product_list;
        }

        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'valid_from' => $validFrom,
            'valid_till' => $validTill,
            'product_list' => $productList,
        ]);
    }

}

