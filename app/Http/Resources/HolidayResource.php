<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * HolidayResource
 *
 * API resource for transforming Holiday model data.
 */
class HolidayResource extends JsonResource
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
             * Holiday ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * User ID who requested the holiday.
             *
             * @var int $user_id
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * Start date of the holiday.
             *
             * @var string $from_date
             * @example 2024-01-01
             */
            'from_date' => $this->from_date?->format('Y-m-d'),

            /**
             * End date of the holiday.
             *
             * @var string $to_date
             * @example 2024-01-05
             */
            'to_date' => $this->to_date?->format('Y-m-d'),

            /**
             * Optional note about the holiday.
             *
             * @var string|null $note
             * @example Annual leave
             */
            'note' => $this->note,

            /**
             * Whether the holiday is approved.
             *
             * @var bool $is_approved
             * @example false
             */
            'is_approved' => $this->is_approved,

            /**
             * Whether the holiday is recurring.
             *
             * @var bool $recurring
             * @example false
             */
            'recurring' => $this->recurring,

            /**
             * Region where the holiday applies.
             *
             * @var string|null $region
             * @example US
             */
            'region' => $this->region,

            /**
             * Timestamp when the holiday was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the holiday was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

