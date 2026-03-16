<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
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
             * The unique identifier for the product.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the product.
             *
             * @example iPhone 15 Pro
             */
            'name' => $this->name,

            /**
             * The product code or SKU.
             *
             * @example IPH15PRO
             */
            'code' => $this->code,

            /**
             * The product type.
             *
             * @example standard
             */
            'type' => $this->type,

            'slug' => $this->slug,
            'barcode_symbology' => $this->barcode_symbology,

            'cost' => (float) $this->cost,
            'price' => (float) $this->price,
            'wholesale_price' => $this->wholesale_price ? (float) $this->wholesale_price : null,
            'profit_margin' => $this->profit_margin ? (float) $this->profit_margin : null,
            'profit_margin_type' => $this->profit_margin_type,

            'qty' => (float) $this->qty,
            'alert_quantity' => $this->alert_quantity ? (float) $this->alert_quantity : null,

            'is_active' => (bool) $this->is_active,
            'featured' => (bool) $this->featured,

            'image_path' => $this->image_path,
            'image_url' => $this->image_url,

            // Relationships
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],

            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
            ],

            'unit' => [
                'id' => $this->unit?->id,
                'name' => $this->unit?->name,
                'code' => $this->unit?->unit_code,
            ],

            /**
             * The date and time when the product was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the product was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
