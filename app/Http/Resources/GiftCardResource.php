<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GiftCardResource
 *
 * API resource for transforming GiftCard model data.
 */
class GiftCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            /**
             * Gift Card ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Unique gift card number.
             *
             * @var string $card_no
             * @example 1234567890123456
             */
            'card_no' => $this->card_no,

            /**
             * Gift card amount.
             *
             * @var float $amount
             * @example 100.00
             */
            'amount' => $this->amount,

            /**
             * Amount spent from the gift card.
             *
             * @var float $expense
             * @example 25.50
             */
            'expense' => $this->expense,

            /**
             * Customer ID (if assigned to customer).
             *
             * @var int|null $customer_id
             * @example 1
             */
            'customer_id' => $this->customer_id,

            /**
             * User ID (if assigned to user).
             *
             * @var int|null $user_id
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * Expiration date of the gift card.
             *
             * @var string|null $expired_date
             * @example 2024-12-31
             */
            'expired_date' => $this->expired_date?->format('Y-m-d'),

            /**
             * ID of user who created the gift card.
             *
             * @var int|null $created_by
             * @example 1
             */
            'created_by' => $this->created_by,

            /**
             * Whether the gift card is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the gift card was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the gift card was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

