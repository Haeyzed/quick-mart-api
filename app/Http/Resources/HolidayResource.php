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
     * @param Request $request
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
             * The ID of the user who requested the holiday.
             *
             * @example 5
             */
            'user_id' => $this->user_id,

            /**
             * The name of the user (if relation is loaded).
             *
             * @example John Doe
             */
            'user_name' => $this->whenLoaded('user', fn() => $this->user->name),

            /**
             * The start date of the holiday.
             *
             * @example 2024-12-25
             */
            'from_date' => $this->from_date?->format('Y-m-d'),

            /**
             * The end date of the holiday.
             *
             * @example 2024-12-26
             */
            'to_date' => $this->to_date?->format('Y-m-d'),

            /**
             * The note or reason for the holiday.
             *
             * @example Christmas Holiday
             */
            'note' => $this->note,

            /**
             * Indicates if the holiday repeats annually.
             *
             * @example true
             */
            'recurring' => $this->recurring,

            /**
             * The region this holiday applies to.
             *
             * @example Global
             */
            'region' => $this->region,

            /**
             * Indicates if the holiday request is approved.
             *
             * @example true
             */
            'is_approved' => $this->is_approved,

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
        ];
    }
}
