<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UserResource
 *
 * Transforms a User model instance into a JSON response with full documentation
 * for each field to ensure API documentation clarity.
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            /**
             * The unique identifier for the user.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The user's name.
             *
             * @var string $name
             * @example John Doe
             */
            'name' => $this->name,

            /**
             * The user's username.
             *
             * @var string|null $username
             * @example john_doe
             */
            'username' => $this->username,

            /**
             * The user's email address.
             *
             * @var string|null $email
             * @example john.doe@example.com
             */
            'email' => $this->email,

            /**
             * The user's avatar image path.
             *
             * @var string|null $avatar
             * @example images/user/avatar/avatar.jpg
             */
            'avatar' => $this->avatar,

            /**
             * The user's avatar image URL.
             *
             * @var string|null $avatar_url
             * @example https://example.com/storage/images/user/avatar/avatar.jpg
             */
            'avatar_url' => $this->avatar_url,

            /**
             * The user's phone number.
             *
             * @var string|null $phone
             * @example +1234567890
             */
            'phone' => $this->phone,

            /**
             * The user's company name.
             *
             * @var string|null $company_name
             * @example Acme Corporation
             */
            'company_name' => $this->company_name,

            /**
             * The user's biller ID.
             *
             * @var int|null $biller_id
             * @example 1
             */
            'biller_id' => $this->biller_id,

            /**
             * The user's warehouse ID.
             *
             * @var int|null $warehouse_id
             * @example 1
             */
            'warehouse_id' => $this->warehouse_id,

            /**
             * Whether the user is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => (bool)$this->is_active,

            /**
             * Whether the user is deleted.
             *
             * @var bool $is_deleted
             * @example false
             */
            'is_deleted' => (bool)$this->is_deleted,

            /**
             * ISO 8601 formatted email verification timestamp.
             *
             * @var string|null $email_verified_at
             * @example 2024-01-15T10:30:00.000000Z
             */
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),

            /**
             * ISO 8601 formatted creation timestamp.
             *
             * @var string|null $created_at
             * @example 2024-01-15T10:30:00.000000Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * ISO 8601 formatted last update timestamp.
             *
             * @var string|null $updated_at
             * @example 2024-01-15T15:45:00.000000Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Biller relationship (if loaded).
             *
             * @var array<string, mixed>|null $biller
             */
            'biller' => $this->whenLoaded('biller'),

            /**
             * Warehouse relationship (if loaded).
             *
             * @var array<string, mixed>|null $warehouse
             */
            'warehouse' => $this->whenLoaded('warehouse'),

            /**
             * Role assigned to the user.
             * Returns array of role objects with id and name only.
             *
             * @var array<int, array{id: int, name: string}>|null $roles
             * @example [{"id": 1, "name": "Admin"}]
             */
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                })->values();
            }),

            /**
             * Direct permissions assigned to the user (excluding role-based permissions).
             * Returns array of permission objects with id and name only.
             *
             * @var array<int, array{id: int, name: string}>|null $permissions
             * @example [{"id": 4, "name": "products-edit"}]
             */
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->values();
            }),

            /**
             * All resolved permissions for the user (direct + role-based, deduplicated).
             * This is the complete list of permissions the user has access to.
             * Optimized for frontend authorization checks.
             *
             * @var array<string> $user_permissions
             * @example ["products-index", "products-add", "products-edit", "products-delete", "category", "brand", "unit", "tax"]
             */
            'user_permissions' => $this->getAllUserPermissions()
                ->pluck('name')
                ->unique()
                ->values()
                ->toArray(),

            /**
             * Role names as a simple array for easy frontend consumption.
             *
             * @var array<string> $role_names
             * @example ["Admin", "Owner"]
             */
            'role_names' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name')->toArray();
            }),
        ];
    }
}

