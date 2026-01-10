<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * CategoryResource
 *
 * Transforms a Category model instance into a JSON response with full documentation
 * for each field to ensure API documentation clarity.
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
             * The unique identifier for the category.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The category name. Must be unique across all categories.
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
            'image_url' => $this->image_url ?? ($this->image ? Storage::disk('public')->url('categories/' . $this->image) : null),

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
            'icon_url' => $this->icon_url ?? ($this->icon ? Storage::disk('public')->url('categories/icons/' . $this->icon) : null),

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
             * Whether the category is active and visible to users.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => (bool)$this->is_active,

            /**
             * Whether the category is featured on the homepage.
             *
             * @var bool $featured
             * @example false
             */
            'featured' => (bool)$this->featured,

            /**
             * Whether sync to external systems is disabled.
             *
             * @var bool $is_sync_disable
             * @example false
             */
            'is_sync_disable' => (bool)$this->is_sync_disable,

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
             * ISO 8601 formatted creation timestamp.
             *
             * @var string|null $created_at
             * @example 2024-01-15T10:30:00.000000Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * ISO 8601 formatted last update timestamp.
             *
             * @var string|null $updated_at
             * @example 2024-01-15T15:45:00.000000Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * ISO 8601 formatted deletion timestamp (if soft deleted).
             *
             * @var string|null $deleted_at
             * @example 2024-01-20T12:00:00.000000Z
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}

