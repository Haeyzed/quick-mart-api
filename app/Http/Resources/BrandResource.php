<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * BrandResource
 *
 * API resource for transforming Brand model data.
 */
class BrandResource extends JsonResource
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
             * Brand ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Brand name.
             *
             * @var string $name
             * @example Apple
             */
            'name' => $this->name,

            /**
             * URL-friendly slug for the brand.
             *
             * @var string|null $slug
             * @example apple
             */
            'slug' => $this->slug,

            /**
             * Brief description of the brand.
             *
             * @var string|null $short_description
             * @example Premium technology brand
             */
            'short_description' => $this->short_description,

            /**
             * SEO page title for the brand.
             *
             * @var string|null $page_title
             * @example Shop Apple Products | Best Deals
             */
            'page_title' => $this->page_title,

            /**
             * Brand image filename or path.
             *
             * @var string|null $image
             * @example brand-image.jpg
             */
            'image' => $this->image,

            /**
             * Full URL to the brand image.
             *
             * @var string|null $image_url
             * @example https://example.com/images/brand/brand-image.jpg
             */
            'image_url' => $this->image_url,

            /**
             * Active status of the brand.
             *
             * @var bool $is_active
             * @example active
             */
            'is_active' => $this->is_active,

            /**
             * Active status of the brand.
             *
             * @var string $status
             * @example active
             */
            'status' => $this->status,

            /**
             * Timestamp when the brand was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the brand was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

