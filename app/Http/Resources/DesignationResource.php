<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Designation
 */
class DesignationResource extends JsonResource
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
             * The unique identifier for the designation.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the designation.
             *
             * @example Software Engineer
             */
            'name' => $this->name,

            /**
             * Indicates if the designation is active.
             *
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * The active status as a readable string.
             *
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The date and time when the designation was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the designation was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
