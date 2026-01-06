<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CustomerGroupResource
 *
 * API resource for transforming CustomerGroup model data.
 */
class CustomerGroupResource extends JsonResource
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
             * Customer Group ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Customer Group name.
             *
             * @var string $name
             * @example VIP Customers
             */
            'name' => $this->name,

            /**
             * Discount percentage for this customer group.
             *
             * @var float $percentage
             * @example 10.5
             */
            'percentage' => $this->percentage,

            /**
             * Whether the customer group is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the customer group was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the customer group was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

