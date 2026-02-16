<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Country
 */
class CountryResource extends JsonResource
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
            'iso2' => $this->iso2,
            'iso3' => $this->iso3,
            'name' => $this->name,
            'native' => $this->native,
            'region' => $this->region,
            'subregion' => $this->subregion,
            'phone_code' => $this->phone_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'emoji' => $this->emoji,
            'emojiU' => $this->emojiU,
            'status' => $this->status,
        ];
    }
}
