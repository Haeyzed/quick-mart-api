<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\IdCardTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class IdCardTemplateResource
 *
 * @mixin IdCardTemplate
 */
class IdCardTemplateResource extends JsonResource
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
             * The unique identifier for the template.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The human-readable name of the template.
             *
             * @example Standard Corporate ID
             */
            'name' => $this->name,

            /**
             * The JSON configuration for the ID card design.
             *
             * @example {"primary_color": "#171f27", "text_color": "#ffffff", "show_qr_code": true}
             */
            'design_config' => $this->design_config,

            /**
             * Indicates if this is the currently active template.
             *
             * @example true
             */
            'is_active' => (bool) $this->is_active,

            /**
             * Creation timestamp.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Update timestamp.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
