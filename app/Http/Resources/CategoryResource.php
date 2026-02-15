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
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'page_title' => $this->page_title,
            'image' => $this->image,
            'icon' => $this->icon,
            'image_url' => $this->image_url,
            'icon_url' => $this->icon_url,
            'is_active' => $this->is_active,
            'featured' => $this->featured,
            'is_sync_disable' => $this->is_sync_disable,
            'woocommerce_category_id' => $this->woocommerce_category_id,
            'active_status' => $this->is_active ? 'active' : 'inactive',
            'featured_status' => $this->featured ? 'yes' : 'no',
            'sync_status' => $this->is_sync_disable ? 'disabled' : 'enabled',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'parent' => [
                'id' => $this->parent?->id,
                'name' => $this->parent?->name,
            ],
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
