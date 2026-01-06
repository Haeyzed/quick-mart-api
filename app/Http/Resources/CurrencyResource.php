<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CurrencyResource
 *
 * API resource for transforming Currency model data.
 */
class CurrencyResource extends JsonResource
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
             * Currency ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Currency name.
             *
             * @var string $name
             * @example US Dollar
             */
            'name' => $this->name,

            /**
             * Currency code (ISO 4217).
             *
             * @var string $code
             * @example USD
             */
            'code' => $this->code,

            /**
             * Currency symbol.
             *
             * @var string|null $symbol
             * @example $
             */
            'symbol' => $this->symbol,

            /**
             * Exchange rate relative to base currency.
             *
             * @var float $exchange_rate
             * @example 1.0
             */
            'exchange_rate' => $this->exchange_rate,

            /**
             * Whether the currency is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the currency was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the currency was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

