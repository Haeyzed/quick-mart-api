<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RewardPointSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'per_point_amount' => $this->per_point_amount !== null ? (float)$this->per_point_amount : null,
            'minimum_amount' => $this->minimum_amount !== null ? (float)$this->minimum_amount : null,
            'duration' => $this->duration,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'redeem_amount_per_unit_rp' => $this->redeem_amount_per_unit_rp !== null ? (float)$this->redeem_amount_per_unit_rp : null,
            'min_order_total_for_redeem' => $this->min_order_total_for_redeem !== null ? (float)$this->min_order_total_for_redeem : null,
            'min_redeem_point' => $this->min_redeem_point,
            'max_redeem_point' => $this->max_redeem_point,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
