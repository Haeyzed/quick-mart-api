<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * GiftCardRechargeRequest
 *
 * Validates incoming data for recharging a gift card.
 */
class GiftCardRechargeRequest extends FormRequest
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
             * Amount to recharge the gift card with.
             *
             * @var float @amount
             * @example 50.00
             */
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}

