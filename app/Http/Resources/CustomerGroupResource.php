<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CustomerGroup
 */
class CustomerGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * The unique identifier for the customer group.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the customer group.
             *
             * @example Wholesale
             */
            'name' => $this->name,

            /**
             * The discount percentage for the group.
             *
             * @example 10.5
             */
            'percentage' => $this->percentage,

            /**
             * Indicates if the customer group is active.
             *
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * The active status as a readable string.
             *
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The date and time when the customer group was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the customer group was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
