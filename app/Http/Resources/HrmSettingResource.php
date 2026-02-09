<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for HrmSetting entity.
 *
 * Transforms HrmSetting model into a consistent JSON structure for API responses.
 *
 * @mixin \App\Models\HrmSetting
 */
class HrmSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed HRM setting data for API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'checkin' => $this->checkin,
            'checkout' => $this->checkout,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
