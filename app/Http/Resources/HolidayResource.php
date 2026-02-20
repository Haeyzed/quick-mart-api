<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Holiday
 */
class HolidayResource extends JsonResource
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
             * The unique identifier for the holiday.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The user ID the holiday belongs to.
             *
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * Start date of the holiday period (Y-m-d).
             *
             * @example 2024-12-25
             */
            'from_date' => $this->from_date?->format('Y-m-d'),

            /**
             * End date of the holiday period (Y-m-d).
             *
             * @example 2024-12-31
             */
            'to_date' => $this->to_date?->format('Y-m-d'),

            /**
             * Optional note or reason for the holiday.
             *
             * @example Annual leave
             */
            'note' => $this->note,

            /**
             * Whether the holiday is approved.
             *
             * @example true
             */
            'is_approved' => $this->is_approved,

            /**
             * Whether the holiday recurs.
             *
             * @example false
             */
            'recurring' => $this->recurring,

            /**
             * Optional region or location for the holiday.
             *
             * @example HQ
             */
            'region' => $this->region,

            /**
             * The date and time when the holiday was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the holiday was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * The user relation when loaded (id, name, email).
             *
             * @example {"id":1,"name":"John Doe","email":"john@example.com"}
             */
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
        ];
    }
}
