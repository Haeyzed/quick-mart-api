<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PermissionResource
 *
 * @mixin Permission
 */
class PermissionResource extends JsonResource
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
             * The unique identifier for the permission.
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the permission.
             * @example view employees
             */
            'name' => $this->name,

            /**
             * The auth guard applied to the permission.
             * @example web
             */
            'guard_name' => $this->guard_name,

            /**
             * The related module.
             * @example hrm
             */
            'module' => $this->module,

            /**
             * The description of the permission.
             * @example Full access to human resources.
             */
            'description' => $this->description,

            /**
             * Indicates if the permission is active.
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Human-readable active status.
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

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
