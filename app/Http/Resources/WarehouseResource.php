<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Warehouse
 */
class WarehouseResource extends JsonResource
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
             * The unique identifier for the warehouse.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the warehouse.
             *
             * @example Main Store
             */
            'name' => $this->name,

            /**
             * Contact phone number for the warehouse.
             *
             * @example +1234567890
             */
            'phone_number' => $this->phone_number,

            /**
             * Contact email for the warehouse.
             *
             * @example warehouse@example.com
             */
            'email' => $this->email,

            /**
             * Physical address of the warehouse.
             *
             * @example 123 Storage Lane
             */
            'address' => $this->address,

            /**
             * Indicates if the warehouse is active.
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
             * Number of products with stock > 0 in this warehouse (when loaded via withCount).
             *
             * @example 42
             */
            'number_of_products' => $this->number_of_products ?? $this->productWarehouses()->where('qty', '>', 0)->count(),

            /**
             * Total stock quantity (sum of qty) for products with qty > 0 (when loaded via withSum).
             *
             * @example 1500.5
             */
            'stock_quantity' => (float) ($this->stock_quantity ?? $this->productWarehouses()->where('qty', '>', 0)->sum('qty')),

            /**
             * The date and time when the warehouse was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the warehouse was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
