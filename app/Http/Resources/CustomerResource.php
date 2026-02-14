<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_group_id' => $this->customer_group_id,
            'customer_group' => $this->whenLoaded('customerGroup', fn() => new CustomerGroupResource($this->customerGroup)),
            'user_id' => $this->user_id,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'type' => $this->type,
            'phone_number' => $this->phone_number,
            'wa_number' => $this->wa_number,
            'tax_no' => $this->tax_no,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'opening_balance' => $this->opening_balance,
            'credit_limit' => $this->credit_limit,
            'points' => $this->points,
            'deposit' => $this->deposit,
            'deposited_balance' => round((float)$this->deposit - (float)$this->expense, 2),
            'total_due' => $this->when(isset($this->resource->total_due), round((float)$this->resource->total_due, 2)),
            'pay_term_no' => $this->pay_term_no,
            'pay_term_period' => $this->pay_term_period,
            'expense' => $this->expense,
            'is_active' => $this->is_active,
            'discount_plans' => $this->whenLoaded('discountPlans', fn() => $this->discountPlans->pluck('name')->toArray()),
            'custom_fields' => $this->resource->getCustomFieldValues(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
