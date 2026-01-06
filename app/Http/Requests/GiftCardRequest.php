<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * GiftCardRequest
 *
 * Validates incoming data for both creating and updating gift cards.
 */
class GiftCardRequest extends BaseRequest
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
        $giftCardId = $this->route('gift_card');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            /**
             * Unique gift card number. If not provided, will be auto-generated.
             *
             * @var string|null @card_no
             * @example 1234567890123456
             */
            'card_no' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('gift_cards', 'card_no')->where(function ($query) {
                    return $query->where('is_active', true);
                })->ignore($giftCardId),
            ],
            /**
             * Gift card amount.
             *
             * @var float|null @amount
             * @example 100.00
             */
            'amount' => [$isUpdate ? 'nullable' : 'required', 'numeric', 'min:0'],
            /**
             * Customer ID (if assigned to customer).
             *
             * @var int|null @customer_id
             * @example 1
             */
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')],
            /**
             * User ID (if assigned to user).
             *
             * @var int|null @user_id
             * @example 1
             */
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            /**
             * Expiration date of the gift card.
             *
             * @var string|null @expired_date
             * @example 2024-12-31
             */
            'expired_date' => ['nullable', 'date'],
            /**
             * Whether the gift card is active.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Helper field to indicate if assigned to user (if true, customer_id will be null).
             *
             * @var bool|null @user
             * @example true
             */
            'user' => ['nullable', 'boolean'],
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
            'card_no' => $this->card_no ? trim($this->card_no) : null,
        ]);
    }

}

