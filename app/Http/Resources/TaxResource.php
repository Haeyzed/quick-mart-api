<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TaxResource
 *
 * API resource for transforming Tax model data.
 */
class TaxResource extends JsonResource
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
             * Tax ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Tax name.
             *
             * @var string $name
             * @example VAT
             */
            'name' => $this->name,

            /**
             * Tax rate as a percentage.
             *
             * @var float $rate
             * @example 15.5
             */
            'rate' => $this->rate,

            /**
             * Whether the tax is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * WooCommerce tax ID for sync purposes.
             *
             * @var int|null $woocommerce_tax_id
             * @example 123
             */
            'woocommerce_tax_id' => $this->woocommerce_tax_id,

            /**
             * Timestamp when the tax was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the tax was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

