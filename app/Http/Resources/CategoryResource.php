<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Category entity.
 *
 * Transforms Category model into a consistent JSON structure for API responses.
 * Compatible with Scramble/OpenAPI documentation.
 *
 * @mixin \App\Models\Category
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed category data for API response.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'page_title' => $this->page_title,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'icon' => $this->icon,
            'icon_url' => $this->icon_url,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->parent?->name,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'is_featured' => $this->featured,
            'featured_status' => $this->featured_status,
            'is_sync_disable' => $this->is_sync_disable,
            'sync_status' => $this->sync_status,
            'woocommerce_category_id' => $this->woocommerce_category_id,
            'is_root' => $this->isRoot(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
