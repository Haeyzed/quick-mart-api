<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DiscountResource
 *
 * API resource for transforming Discount model data.
 */
class DiscountResource extends JsonResource
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
             * Discount ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Discount name.
             *
             * @var string $name
             * @example Summer Sale
             */
            'name' => $this->name,

            /**
             * What the discount applies to (All or Selected).
             *
             * @var string $applicable_for
             * @example All
             */
            'applicable_for' => $this->applicable_for,

            /**
             * Comma-separated list of product IDs (if applicable_for is Selected).
             *
             * @var string|null $product_list
             * @example 1,2,3
             */
            'product_list' => $this->product_list,

            /**
             * Start date of discount validity.
             *
             * @var string $valid_from
             * @example 2024-01-01
             */
            'valid_from' => $this->valid_from?->format('Y-m-d'),

            /**
             * End date of discount validity.
             *
             * @var string $valid_till
             * @example 2024-12-31
             */
            'valid_till' => $this->valid_till?->format('Y-m-d'),

            /**
             * Discount type (percentage or fixed).
             *
             * @var string $type
             * @example percentage
             */
            'type' => $this->type,

            /**
             * Discount value (percentage or fixed amount).
             *
             * @var float $value
             * @example 10.5
             */
            'value' => $this->value,

            /**
             * Minimum quantity required for discount.
             *
             * @var float|null $minimum_qty
             * @example 1
             */
            'minimum_qty' => $this->minimum_qty,

            /**
             * Maximum quantity allowed for discount.
             *
             * @var float|null $maximum_qty
             * @example 100
             */
            'maximum_qty' => $this->maximum_qty,

            /**
             * Days of week when discount applies (comma-separated).
             *
             * @var string $days
             * @example Mon,Tue,Wed
             */
            'days' => $this->days,

            /**
             * Whether the discount is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the discount was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the discount was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

