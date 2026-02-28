<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WorkLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WorkLocation
 */
class WorkLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'is_active' => (bool) $this->is_active,
            'active_status' => $this->is_active ? 'active' : 'inactive',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
