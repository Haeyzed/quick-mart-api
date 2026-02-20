<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
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
             * The unique identifier for the category.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the category.
             *
             * @example Electronics
             */
            'name' => $this->name,

            /**
             * The URL-friendly slug of the category.
             *
             * @example electronics
             */
            'slug' => $this->slug,

            /**
             * A short description of the category.
             *
             * @example Consumer electronics and gadgets
             */
            'short_description' => $this->short_description,

            /**
             * The SEO page title for the category.
             *
             * @example Electronics - Shop Now
             */
            'page_title' => $this->page_title,

            /**
             * The parent category ID. Null for root.
             *
             * @example 1
             */
            'parent_id' => $this->parent_id,

            /**
             * The raw path to the category's image.
             *
             * @example images/categories/electronics.png
             */
            'image' => $this->image,

            /**
             * The raw path to the category's icon.
             *
             * @example images/categories/icons/electronics.svg
             */
            'icon' => $this->icon,

            /**
             * The fully qualified URL to the category's image.
             *
             * @example https://example.com/storage/images/categories/electronics.png
             */
            'image_url' => $this->image_url,

            /**
             * The fully qualified URL to the category's icon.
             *
             * @example https://example.com/storage/images/categories/icons/electronics.svg
             */
            'icon_url' => $this->icon_url,

            /**
             * Indicates if the category is active.
             *
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Indicates if the category is featured.
             *
             * @example false
             */
            'featured' => $this->featured,

            /**
             * Indicates if sync (e.g. WooCommerce) is disabled for this category.
             *
             * @example false
             */
            'is_sync_disable' => $this->is_sync_disable,

            /**
             * Optional WooCommerce category ID for sync.
             *
             * @example 42
             */
            'woocommerce_category_id' => $this->woocommerce_category_id,

            /**
             * The active status as a readable string.
             *
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The featured status as a readable string.
             *
             * @example yes
             */
            'featured_status' => $this->featured ? 'yes' : 'no',

            /**
             * The sync status as a readable string.
             *
             * @example enabled
             */
            'sync_status' => $this->is_sync_disable ? 'disabled' : 'enabled',

            /**
             * The date and time when the category was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the category was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * The parent category (id and name) when loaded.
             */
            'parent' => [
                'id' => $this->parent?->id,
                'name' => $this->parent?->name,
            ],

            /**
             * Nested child categories when loaded (tree structure).
             */
            'children' => CategoryTreeItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
