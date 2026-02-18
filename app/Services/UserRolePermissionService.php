<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Class UserRolePermissionService
 *
 * Handles role and permission assignment to users, and permission resolution.
 * Used by AuthService, UserService, SocialAuthService, CheckPermissionsTrait.
 */
class UserRolePermissionService extends BaseService
{
    /**
     * Assign roles and permissions to a user with automatic deduplication.
     *
     * @param array<int|string|Role>|null $roles
     * @param array<int|string|Permission>|null $directPermissions
     */
    public function assignRolesAndPermissions(
        User $user,
        ?array $roles = null,
        ?array $directPermissions = null
    ): void {
        $this->transaction(function () use ($user, $roles, $directPermissions) {
            if ($roles !== null && ! empty($roles)) {
                $this->assignRoles($user, $roles);
            }
            $this->syncUserPermissions($user, $directPermissions);
        });
    }

    /**
     * Assign roles to a user.
     *
     * @param array<int|string|Role> $roles
     */
    public function assignRoles(User $user, array $roles): void
    {
        $roleModels = $this->normalizeRoles($roles);
        $user->syncRoles($roleModels);
    }

    /**
     * @param array<int|string|Role> $roles
     * @return array<Role>
     */
    private function normalizeRoles(array $roles): array
    {
        $normalized = [];
        foreach ($roles as $role) {
            if ($role instanceof Role) {
                $normalized[] = $role;
            } elseif (is_numeric($role)) {
                $roleModel = Role::find($role);
                if ($roleModel) {
                    $normalized[] = $roleModel;
                }
            } elseif (is_string($role)) {
                $roleModel = Role::findByName($role);
                if ($roleModel) {
                    $normalized[] = $roleModel;
                }
            }
        }
        return $normalized;
    }

    /**
     * Sync user permissions (from roles + direct permissions).
     *
     * @param array<int|string|Permission>|null $directPermissions
     */
    public function syncUserPermissions(User $user, ?array $directPermissions = null): void
    {
        $rolePermissions = $this->getPermissionsFromRoles($user->roles);
        $directPermissionModels = $directPermissions !== null && ! empty($directPermissions)
            ? $this->normalizePermissions($directPermissions)
            : collect([]);
        $allPermissions = $rolePermissions->merge($directPermissionModels)->unique('id')->values();
        $user->syncPermissions($allPermissions);
    }

    /**
     * @param Collection<int, Role>|SupportCollection<int, Role> $roles
     * @return SupportCollection<int, Permission>
     */
    public function getPermissionsFromRoles(Collection|SupportCollection $roles): SupportCollection
    {
        $permissions = collect();
        foreach ($roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }
        return $permissions->unique('id')->values();
    }

    /**
     * @param array<int|string|Permission> $permissions
     * @return SupportCollection<int, Permission>
     */
    private function normalizePermissions(array $permissions): SupportCollection
    {
        $normalized = collect();
        foreach ($permissions as $permission) {
            if ($permission instanceof Permission) {
                $normalized->push($permission);
            } elseif (is_numeric($permission)) {
                $model = Permission::findById($permission);
                if ($model) {
                    $normalized->push($model);
                }
            } elseif (is_string($permission)) {
                $model = Permission::findByName($permission);
                if ($model) {
                    $normalized->push($model);
                }
            }
        }
        return $normalized;
    }

    public function checkPermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    public function hasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    public function hasAllPermissions(User $user, array $permissions): bool
    {
        return $user->hasAllPermissions($permissions);
    }

    public function hasRole(User $user, string|Role $role): bool
    {
        return $user->hasRole($role);
    }

    public function hasAnyRole(User $user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    public function hasAllRoles(User $user, array $roles): bool
    {
        return $user->hasAllRoles($roles);
    }

    /**
     * @return SupportCollection<int, Permission>
     */
    public function getUserPermissions(User $user): SupportCollection
    {
        return collect($user->getAllPermissions());
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(string $guardName = 'web'): Collection
    {
        return Permission::where('guard_name', $guardName)->get();
    }

    /**
     * @return Collection<int, Role>
     */
    public function getUserRoles(User $user): Collection
    {
        return $user->roles;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getAllRoles(string $guardName = 'web'): Collection
    {
        return Role::where('guard_name', $guardName)->get();
    }

    public function removeRole(User $user, string|Role $role): void
    {
        $this->transaction(function () use ($user, $role) {
            $user->removeRole($role);
            $this->syncUserPermissions($user);
        });
    }

    public function removePermission(User $user, string|Permission $permission): void
    {
        $user->revokePermissionTo($permission);
    }

    public function clearRolesAndPermissions(User $user): void
    {
        $this->transaction(function () use ($user) {
            $user->syncRoles([]);
            $user->syncPermissions([]);
        });
    }
}
