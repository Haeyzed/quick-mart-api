<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleAgentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn () => new DepartmentResource($this->department)),
            'designation_id' => $this->designation_id,
            'designation' => $this->whenLoaded('designation', fn () => new DesignationResource($this->designation)),
            'shift_id' => $this->shift_id,
            'shift' => $this->whenLoaded('shift', fn () => $this->shift ? ['id' => $this->shift->id, 'name' => $this->shift->name ?? null] : null),
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn () => $this->user ? new UserResource($this->user) : null),
            'staff_id' => $this->staff_id,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'basic_salary' => $this->basic_salary,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'is_sale_agent' => $this->is_sale_agent,
            'sale_commission_percent' => $this->sale_commission_percent,
            'sales_target' => $this->sales_target,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
