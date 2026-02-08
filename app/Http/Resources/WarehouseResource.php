<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * WarehouseResource
 *
 * API resource for transforming Warehouse model data.
 */
class WarehouseResource extends JsonResource
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
             * Warehouse ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Warehouse name.
             *
             * @var string $name
             * @example Main Warehouse
             */
            'name' => $this->name,

            /**
             * Warehouse contact phone number.
             *
             * @var string|null $phone
             * @example +1234567890
             */
            'phone' => $this->phone,

            /**
             * Warehouse contact email address.
             *
             * @var string|null $email
             * @example warehouse@example.com
             */
            'email' => $this->email,

            /**
             * Warehouse physical address.
             *
             * @var string|null $address
             * @example 123 Main St, City, State 12345
             */
            'address' => $this->address,

            /**
             * Whether the warehouse is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Number of products with stock (qty > 0) in this warehouse.
             *
             * @var int $number_of_products
             * @example 42
             */
            'number_of_products' => $this->number_of_products ?? $this->productWarehouses()->where('qty', '>', 0)->count(),

            /**
             * Total stock quantity (sum of qty where qty > 0) in this warehouse.
             *
             * @var float $stock_quantity
             * @example 1500.5
             */
            'stock_quantity' => (float) ($this->stock_quantity ?? $this->productWarehouses()->where('qty', '>', 0)->sum('qty')),

            /**
             * Timestamp when the warehouse was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the warehouse was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

