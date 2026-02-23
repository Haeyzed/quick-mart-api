<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LeaveType
 */
class LeaveTypeResource extends JsonResource
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
             * The unique identifier for the leave type.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the leave type.
             *
             * @example Annual Leave
             */
            'name' => $this->name,

            /**
             * The annual limit of days for this leave type.
             *
             * @example 21.5
             */
            'annual_quota' => (float) $this->annual_quota,

            /**
             * Whether the leave type can be converted to monetary value.
             *
             * @example true
             */
            'encashable' => $this->encashable,

            /**
             * The limit of days that can roll over into the next year.
             *
             * @example 5
             */
            'carry_forward_limit' => (float) $this->carry_forward_limit,

            /**
             * Indicates if the leave type is active.
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
             * The date and time when the leave type was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the leave type was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
