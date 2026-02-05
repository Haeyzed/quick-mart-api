<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * CategoryResource
 *
 * Transforms a Category model instance into a JSON response.
 * Provides complete and consistent API response structure.
 */
class CategoryResource extends JsonResource
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
             * Category ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Category name.
             *
             * @var string $name
             * @example Electronics
             */
            'name' => $this->name,

            /**
             * URL-friendly slug for the category.
             *
             * @var string|null $slug
             * @example electronics
             */
            'slug' => $this->slug,

            /**
             * Brief description of the category.
             *
             * @var string|null $short_description
             * @example High-end electronics and gadgets
             */
            'short_description' => $this->short_description,

            /**
             * SEO page title for the category.
             *
             * @var string|null $page_title
             * @example Shop Electronics | Best Deals
             */
            'page_title' => $this->page_title,

            /**
             * Category image filename or path.
             *
             * @var string|null $image
             * @example category-image.jpg
             */
            'image' => $this->image,

            /**
             * Full URL to the category image.
             *
             * @var string|null $image_url
             * @example https://example.com/images/category/category-image.jpg
             */
            'image_url' => $this->image_url,

            /**
             * Category icon filename or path.
             *
             * @var string|null $icon
             * @example category-icon.png
             */
            'icon' => $this->icon,

            /**
             * Full URL to the category icon.
             *
             * @var string|null $icon_url
             * @example https://example.com/storage/categories/icons/category-icon.png
             */
            'icon_url' => $this->icon_url,

            /**
             * Parent category ID for hierarchical relationships.
             *
             * @var int|null $parent_id
             * @example 1
             */
            'parent_id' => $this->parent_id,

            /**
             * Parent category name (if parent exists).
             *
             * @var string|null $parent_name
             * @example Electronics
             */
            'parent_name' => $this->parent?->name,

            /**
            * Active status of the category.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
            * Active status of the category.
             *
             * @var string $status
             * @example active
             */
            'status' => $this->status,
            
            /**
            * Featured status of the category.
            *
            * @var bool $is_featured
            * @example true
            */
           'is_featured' => $this->is_featured,
            
           /**
           * Featured status of the category.
           *
           * @var string $featured_status
           * @example featured
           */
          'featured_status' => $this->featured_status,

           /**
            * Sync status of the category.
            *
            * @var bool $is_sync_disable
            * @example true
            */
           'is_sync_disable'   => $this->is_sync_disable,

           /**
            * Sync status of the category.
            *
            * @var string $sync_status
            * @example enabled
            */
           'sync_status'   => $this->sync_status,

            /**
             * WooCommerce category ID for external system sync.
             *
             * @var int|null $woocommerce_category_id
             * @example 123
             */
            'woocommerce_category_id' => $this->woocommerce_category_id,

            /**
             * Whether this is a root category (no parent).
             *
             * @var bool $is_root
             * @example true
             */
            'is_root' => $this->isRoot(),

            /**
             * Timestamp when the category was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the category was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Timestamp when the category was deleted (if soft deleted).
             *
             * @var string|null $deleted_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
