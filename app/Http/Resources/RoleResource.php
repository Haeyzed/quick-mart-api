<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RoleResource
 *
 * @mixin Role
 */
class RoleResource extends JsonResource
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
            /**
             * The unique identifier for the role.
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the role.
             * @example HR Manager
             */
            'name' => $this->name,

            /**
             * The description of the role.
             * @example Full access to human resources.
             */
            'description' => $this->description,

            /**
             * The auth guard applied to the role.
             * @example web
             */
            'guard_name' => $this->guard_name,

            /**
             * Indicates if the role is active.
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Human-readable active status.
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The total count of permissions assigned to this role.
             * @example 15
             */
            'permissions_count' => $this->when(isset($this->permissions_count), fn () => $this->permissions_count),

            /**
             * The associated permissions mapping.
             * @example [{"id": 1, "name": "view employees"}]
             */
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name
            ])),

            /**
             * Creation timestamp.
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Update timestamp.
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
