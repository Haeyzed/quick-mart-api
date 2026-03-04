<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DocumentType
 */
class DocumentTypeResource extends JsonResource
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
             * The unique identifier for the document type.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the document type.
             *
             * @example Passport
             */
            'name' => $this->name,

            /**
             * The code of the document type.
             *
             * @example PASS
             */
            'code' => $this->code,

            /**
             * Indicates if the document type requires an expiry date.
             *
             * @example true
             */
            'requires_expiry' => (bool)$this->requires_expiry,

            /**
             * Indicates if the document type is active.
             *
             * @example true
             */
            'is_active' => (bool)$this->is_active,

            /**
             * The human-readable active status.
             *
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The date and time when the document type was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the document type was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
