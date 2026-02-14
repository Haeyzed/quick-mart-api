<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Tax entity.
 *
 * Transforms Tax model into a consistent JSON structure for API responses.
 * Compatible with Scramble/OpenAPI documentation.
 *
 * @mixin Tax
 */
class TaxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed tax data for API response.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'woocommerce_tax_id' => $this->woocommerce_tax_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

