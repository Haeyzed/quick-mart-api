<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CourierResource
 *
 * API resource for transforming Courier model data.
 */
class CourierResource extends JsonResource
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
             * Courier ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Courier name.
             *
             * @var string $name
             * @example DHL Express
             */
            'name' => $this->name,

            /**
             * Courier contact phone number.
             *
             * @var string|null $phone_number
             * @example +1234567890
             */
            'phone_number' => $this->phone_number,

            /**
             * Courier address.
             *
             * @var string|null $address
             * @example 123 Main St, City, State 12345
             */
            'address' => $this->address,

            /**
             * Whether the courier is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the courier was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the courier was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

