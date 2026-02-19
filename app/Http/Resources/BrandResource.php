<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Brand
 */
class BrandResource extends JsonResource
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
            /**
             * The unique identifier for the brand.
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the brand.
             * @example Apple
             */
            'name' => $this->name,

            /**
             * The URL-friendly slug of the brand.
             * @example apple
             */
            'slug' => $this->slug,

            /**
             * A short description of the brand.
             * @example Technology company
             */
            'short_description' => $this->short_description,

            /**
             * The SEO page title for the brand.
             * @example Apple - Official Site
             */
            'page_title' => $this->page_title,

            /**
             * The raw path to the brand's image.
             * @example images/brands/apple.png
             */
            'image' => $this->image,

            /**
             * The fully qualified URL to the brand's image.
             * @example https://example.com/storage/images/brands/apple.png
             */
            'image_url' => $this->image_url,

            /**
             * Indicates if the brand is active.
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * The active status as a readable string.
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The start date for the brand's validity.
             * @example 2024-01-01T00:00:00Z
             */
            'start_date' => $this->start_date?->toIso8601String(),

            /**
             * The end date for the brand's validity.
             * @example 2024-12-31T23:59:59Z
             */
            'end_date' => $this->end_date?->toIso8601String(),

            /**
             * The date and time when the brand was created.
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the brand was last updated.
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
