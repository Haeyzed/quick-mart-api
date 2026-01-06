<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DiscountPlanResource
 *
 * API resource for transforming DiscountPlan model data.
 */
class DiscountPlanResource extends JsonResource
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
             * Discount Plan ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Discount plan name.
             *
             * @var string $name
             * @example VIP Plan
             */
            'name' => $this->name,

            /**
             * Type of discount plan (generic or limited).
             *
             * @var string $type
             * @example limited
             */
            'type' => $this->type,

            /**
             * Whether the discount plan is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the discount plan was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the discount plan was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

