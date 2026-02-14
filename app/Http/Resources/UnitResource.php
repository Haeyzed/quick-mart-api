<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Unit entity.
 *
 * Transforms Unit model into a consistent JSON structure for API responses.
 * Compatible with Scramble/OpenAPI documentation.
 *
 * @mixin Unit
 */
class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed unit data for API response.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'base_unit' => $this->base_unit,
            'base_unit_relation' => $this->whenLoaded('baseUnitRelation', function () {
                return [
                    'id' => $this->baseUnitRelation->id,
                    'code' => $this->baseUnitRelation->code,
                    'name' => $this->baseUnitRelation->name,
                ];
            }),
            'operator' => $this->operator,
            'operation_value' => $this->operation_value,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

