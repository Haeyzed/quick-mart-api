<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * API Resource for Supplier entity.
 * Follows the same pattern as CustomerResource: country_id, state_id, city_id and whenLoaded country, state, city.
 *
 * @mixin Supplier
 */
class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'vat_number' => $this->vat_number,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'wa_number' => $this->wa_number,
            'address' => $this->address,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'country' => $this->whenLoaded('country', fn () => $this->country ? ['id' => $this->country->id, 'name' => $this->country->name] : null),
            'state' => $this->whenLoaded('state', fn () => $this->state ? ['id' => $this->state->id, 'name' => $this->state->name] : null),
            'city' => $this->whenLoaded('city', fn () => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null),
            'postal_code' => $this->postal_code,
            'opening_balance' => $this->opening_balance,
            'pay_term_no' => $this->pay_term_no,
            'pay_term_period' => $this->pay_term_period,
            'image' => $this->image,
            'image_url' => $this->image_url ?? ($this->image ? Storage::disk('public')->url($this->image) : null),
            'is_active' => $this->is_active,
            'active_status' => $this->is_active ? 'active' : 'inactive',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
