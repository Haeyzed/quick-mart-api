<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BrandResource
 *
 * API Resource for formatting Brand data.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $page_title
 * @property string|null $image
 * @property string|null $image_url
 * @property bool $is_active
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
             * The unique identifier of the brand.
             * @example 1
             */
            'id' => $this->id,

            /**
             * The display name of the brand.
             * @example "Nike"
             */
            'name' => $this->name,

            /**
             * The URL-friendly slug.
             * @example "nike"
             */
            'slug' => $this->slug,

            /**
             * A short summary of the brand.
             * @example "Just Do It"
             */
            'short_description' => $this->short_description,

            /**
             * The SEO page title.
             * @example "Nike Shoes & Apparel"
             */
            'page_title' => $this->page_title,

            /**
             * The image filename.
             * @example "brands/nike.jpg"
             */
            'image' => $this->image,

            /**
             * The full URL to the brand image.
             * @example "https://api.example.com/storage/brands/nike.jpg"
             */
            'image_url' => $this->image_url,

            /**
             * The boolean active status.
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * The human-readable status.
             * @example "active"
             */
            'status' => $this->status,

            /**
             * Creation timestamp in ISO 8601.
             * @example "2024-01-01T12:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Last update timestamp in ISO 8601.
             * @example "2024-01-02T12:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}