<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tax
 */
class TaxResource extends JsonResource
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
             * The unique identifier for the tax.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the tax.
             *
             * @example VAT 10%
             */
            'name' => $this->name,

            /**
             * The tax rate as a percentage.
             *
             * @example 10
             */
            'rate' => $this->rate,

            /**
             * Indicates if the tax is active.
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
             * Optional WooCommerce tax ID for sync.
             *
             * @example 1
             */
            'woocommerce_tax_id' => $this->woocommerce_tax_id,

            /**
             * The date and time when the tax was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the tax was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
