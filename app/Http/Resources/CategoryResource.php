<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CategoryResource
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $page_title
 * @property string|null $image
 * @property string|null $image_url
 * @property string|null $icon
 * @property string|null $icon_url
 * @property int|null $parent_id
 * @property bool $is_active
 * @property string $status
 * @property bool $featured
 * @property string $featured_status
 * @property bool $is_sync_disable
 * @property string $sync_status
 * @property int|null $woocommerce_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CategoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            /** @example 1 */
            'id' => $this->id,
            /** @example "Electronics" */
            'name' => $this->name,
            /** @example "electronics" */
            'slug' => $this->slug,
            /** @example "Gadgets and more" */
            'short_description' => $this->short_description,
            /** @example "Buy Electronics" */
            'page_title' => $this->page_title,
            /** @example "cat.jpg" */
            'image' => $this->image,
            /** @example "https://site.com/storage/cat.jpg" */
            'image_url' => $this->image_url,
            /** @example "icon.png" */
            'icon' => $this->icon,
            /** @example "https://site.com/storage/icon.png" */
            'icon_url' => $this->icon_url,
            /** @example 5 */
            'parent_id' => $this->parent_id,
            /** @example "Computers" */
            'parent_name' => $this->parent?->name,
            /** @example true */
            'is_active' => $this->is_active,
            /** @example "active" */
            'status' => $this->status,
            /** @example true */
            'is_featured' => $this->featured,
            /** @example "featured" */
            'featured_status' => $this->featured_status,
            /** @example false */
            'is_sync_disable' => $this->is_sync_disable,
            /** @example "enabled" */
            'sync_status' => $this->sync_status,
            /** @example 120 */
            'woocommerce_category_id' => $this->woocommerce_category_id,
            /** @example true */
            'is_root' => $this->isRoot(),
            /** @example "2024-01-01T12:00:00+00:00" */
            'created_at' => $this->created_at?->toIso8601String(),
            /** @example "2024-01-02T12:00:00+00:00" */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}