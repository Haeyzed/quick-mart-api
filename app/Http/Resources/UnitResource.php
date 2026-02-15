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
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'base_unit' => $this->base_unit,
            'operator' => $this->operator,
            'operation_value' => $this->operation_value,
            'is_active' => $this->is_active,
            'active_status' => $this->is_active ? 'active' : 'inactive',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
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
