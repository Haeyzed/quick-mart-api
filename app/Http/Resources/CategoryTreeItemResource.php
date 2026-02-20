<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CategoryTreeItemResource
 *
 * Minimal resource for category tree children (id, name, icon_url, and nested children).
 * Used when embedding category trees in CategoryResource.
 */
class CategoryTreeItemResource extends JsonResource
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
            'id' => (string) $this->id,

            /**
             * The name of the category.
             *
             * @example Electronics
             */
            'name' => $this->name,

            /**
             * The fully qualified URL to the category's icon.
             *
             * @example https://example.com/storage/images/categories/icons/electronics.svg
             */
            'icon_url' => $this->icon_url,

            /**
             * Nested child categories when loaded.
             */
            'children' => CategoryTreeItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
