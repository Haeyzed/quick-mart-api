<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Unit
 */
class UnitResource extends JsonResource
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
             * The unique identifier for the unit.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The short code of the unit.
             *
             * @example kg
             */
            'code' => $this->code,

            /**
             * The display name of the unit.
             *
             * @example Kilogram
             */
            'name' => $this->name,

            /**
             * The base unit ID for conversion. Null for a base unit.
             *
             * @example 1
             */
            'base_unit' => $this->base_unit,

            /**
             * The operator for conversion (e.g. *, /, +, -).
             *
             * @example *
             */
            'operator' => $this->operator,

            /**
             * The numeric value for conversion to base unit.
             *
             * @example 1000
             */
            'operation_value' => $this->operation_value,

            /**
             * Indicates if the unit is active.
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
             * The date and time when the unit was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the unit was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * The base unit relation (id, code, name) when loaded.
             */
            'base_unit_relation' => $this->whenLoaded('baseUnitRelation', function () {
                return [
                    'id' => $this->baseUnitRelation->id,
                    'code' => $this->baseUnitRelation->code,
                    'name' => $this->baseUnitRelation->name,
                ];
            }),
        ];
    }
}
