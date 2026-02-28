<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SalaryStructure
 */
class SalaryStructureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'pay_frequency' => $this->pay_frequency,
            'is_active' => (bool) $this->is_active,
            'structure_items' => $this->whenLoaded('structureItems', fn () => $this->structureItems->map(fn ($item) => [
                'id' => $item->id,
                'salary_component_id' => $item->salary_component_id,
                'amount' => (float) $item->amount,
                'percentage' => $item->percentage ? (float) $item->percentage : null,
                'salary_component' => $this->when($item->relationLoaded('salaryComponent'), fn () => [
                    'id' => $item->salaryComponent->id,
                    'name' => $item->salaryComponent->name,
                    'type' => $item->salaryComponent->type,
                ]),
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
