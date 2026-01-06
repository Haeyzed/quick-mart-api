<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UnitResource
 *
 * API resource for transforming Unit model data.
 */
class UnitResource extends JsonResource
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
             * Unit ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Unique code identifier for the unit.
             *
             * @var string $code
             * @example KG
             */
            'code' => $this->code,

            /**
             * Display name of the unit.
             *
             * @var string $name
             * @example Kilogram
             */
            'name' => $this->name,

            /**
             * Base unit ID for conversion. Null if this is a base unit.
             *
             * @var int|null $base_unit
             * @example 1
             */
            'base_unit' => $this->base_unit,

            /**
             * Base unit relationship data.
             *
             * @var array|null $base_unit_relation
             */
            'base_unit_relation' => $this->whenLoaded('baseUnitRelation', function () {
                return [
                    'id' => $this->baseUnitRelation->id,
                    'code' => $this->baseUnitRelation->code,
                    'name' => $this->baseUnitRelation->name,
                ];
            }),

            /**
             * Mathematical operator for conversion (*, /, +, -).
             *
             * @var string|null $operator
             * @example *
             */
            'operator' => $this->operator,

            /**
             * Value to use with operator for conversion.
             *
             * @var float|null $operation_value
             * @example 1000
             */
            'operation_value' => $this->operation_value,

            /**
             * Whether the unit is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the unit was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the unit was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

