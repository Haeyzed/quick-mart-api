<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin City
 */
class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'country_code' => $this->country_code,
            'state_code' => $this->state_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'country' => $this->whenLoaded('country', fn () => $this->country ? ['id' => $this->country->id, 'name' => $this->country->name] : null),
            'state' => $this->whenLoaded('state', fn () => $this->state ? ['id' => $this->state->id, 'name' => $this->state->name] : null),
        ];
    }
}
