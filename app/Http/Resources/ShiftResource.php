<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shift
 */
class ShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * The unique identifier for the shift.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the shift.
             *
             * @example Morning Shift
             */
            'name' => $this->name,

            /**
             * The start time of the shift.
             *
             * @example 08:00
             */
            'start_time' => $this->start_time,

            /**
             * The end time of the shift.
             *
             * @example 16:00
             */
            'end_time' => $this->end_time,

            /**
             * The grace period allowed for late check-in (in minutes).
             *
             * @example 15
             */
            'grace_in' => (int) $this->grace_in,

            /**
             * The grace period allowed for early check-out (in minutes).
             *
             * @example 10
             */
            'grace_out' => (int) $this->grace_out,

            /**
             * The total number of hours required for this shift.
             *
             * @example 8.0
             */
            'total_hours' => (float) $this->total_hours,

            /**
             * Indicates if the shift is active.
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
             * The date and time when the shift was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the shift was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
