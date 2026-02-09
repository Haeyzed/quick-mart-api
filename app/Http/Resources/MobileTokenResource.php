<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MobileTokenResource
 *
 * API resource for transforming MobileToken model data.
 */
class MobileTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? 'Device',
            'ip' => $this->ip,
            'location' => $this->location,
            'last_active' => $this->last_active?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
