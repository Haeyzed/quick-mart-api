<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'biller_id' => $this->biller_id,
            'warehouse_id' => $this->warehouse_id,
            'is_active' => (bool)$this->is_active,
            'is_deleted' => (bool)$this->is_deleted,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'biller' => $this->whenLoaded('biller'),
            'warehouse' => $this->whenLoaded('warehouse'),
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values()),
            'permissions' => $this->whenLoaded('permissions', fn() => $this->permissions->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])->values()),
            'user_permissions' => $this->getAllUserPermissions()
                ->pluck('name')
                ->unique()
                ->values()
                ->toArray(),
            'role_names' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')->toArray()),
        ];
    }
}
