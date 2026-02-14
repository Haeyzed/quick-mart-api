<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * PermissionService
 *
 * Handles role and permission assignment, deduplication, and permission resolution.
 * Provides centralized permission management following Spatie Roles & Permissions best practices.
 */
class PermissionService extends BaseService
{
    /**
     * Assign roles and permissions to a user with automatic deduplication.
     *
     * This method:
     * 1. Assigns the specified roles to the user
     * 2. Collects all permissions from the assigned roles
     * 3. Merges with any direct permissions provided
     * 4. Deduplicates permissions
     * 5. Syncs all unique permissions to the user
     *
     * @param User $user The user to assign roles and permissions to
     * @param array<int|string|Role>|null $roles Array of role IDs, names, or Role model instances
     * @param array<int|string|Permission>|null $directPermissions Array of permission IDs, names, or Permission model instances
     * @return void
     */
    public function assignRolesAndPermissions(
        User   $user,
        ?array $roles = null,
        ?array $directPermissions = null
    ): void
    {
        $this->transaction(function () use ($user, $roles, $directPermissions) {
            // Assign roles if provided
            if ($roles !== null && !empty($roles)) {
                $this->assignRoles($user, $roles);
            }

            // Sync all permissions (from roles + direct permissions)
            $this->syncUserPermissions($user, $directPermissions);
        });
    }

    /**
     * Assign roles to a user.
     *
     * @param User $user
     * @param array<int|string|Role> $roles Array of role IDs, names, or Role model instances
     * @return void
     */
    public function assignRoles(User $user, array $roles): void
    {
        // Normalize roles to Role model instances
        $roleModels = $this->normalizeRoles($roles);

        // Sync roles to user (replaces existing roles)
        $user->syncRoles($roleModels);
    }

    /**
     * Normalize role input to Role model instances.
     *
     * Accepts: role IDs, role names, or Role model instances.
     *
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
                // ID
                $roleModel = Role::find($role);
                if ($roleModel) {
                    $normalized[] = $roleModel;
                }
            } elseif (is_string($role)) {
                // Name
                $roleModel = Role::findByName($role);
                if ($roleModel) {
                    $normalized[] = $roleModel;
                }
            }
        }

        return $normalized;
    }

    /**
     * Sync user permissions by collecting from roles and merging with direct permissions.
     *
     * This method automatically:
     * - Collects all permissions from user's roles
     * - Merges with direct permissions if provided
     * - Deduplicates permissions
     * - Syncs to user
     *
     * @param User $user
     * @param array<int|string|Permission>|null $directPermissions Optional direct permissions to add
     * @return void
     */
    public function syncUserPermissions(User $user, ?array $directPermissions = null): void
    {
        // Collect permissions from all user roles
        $rolePermissions = $this->getPermissionsFromRoles($user->roles);

        // Get direct permissions if provided
        $directPermissionModels = $directPermissions !== null && !empty($directPermissions)
            ? $this->normalizePermissions($directPermissions)
            : collect([]);

        // Merge and deduplicate permissions
        $allPermissions = $rolePermissions
            ->merge($directPermissionModels)
            ->unique('id')
            ->values();

        // Sync all unique permissions to user
        $user->syncPermissions($allPermissions);
    }

    /**
     * Get all unique permissions from a collection of roles.
     *
     * @param Collection<int, Role>|SupportCollection<int, Role> $roles
     * @return SupportCollection<int, Permission>
     */
    public function getPermissionsFromRoles(Collection|SupportCollection $roles): SupportCollection
    {
        $permissions = collect();

        foreach ($roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        // Deduplicate by permission ID
        return $permissions->unique('id')->values();
    }

    /**
     * Normalize permission input to Permission model instances.
     *
     * Accepts: permission IDs, permission names, or Permission model instances.
     *
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
                // ID
                $permissionModel = Permission::findById($permission);
                if ($permissionModel) {
                    $normalized->push($permissionModel);
                }
            } elseif (is_string($permission)) {
                // Name
                $permissionModel = Permission::findByName($permission);
                if ($permissionModel) {
                    $normalized->push($permissionModel);
                }
            }
        }

        return $normalized;
    }

    /**
     * Check if a user has a specific permission.
     *
     * This method checks both:
     * - Direct user permissions
     * - Permissions from user's roles
     *
     * @param User $user
     * @param string $permission Permission name or ID
     * @return bool
     */
    public function checkPermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if a user has any of the given permissions.
     *
     * @param User $user
     * @param array<string> $permissions Array of permission names
     * @return bool
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if a user has all of the given permissions.
     *
     * @param User $user
     * @param array<string> $permissions Array of permission names
     * @return bool
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Check if a user has a specific role.
     *
     * @param User $user
     * @param string|Role $role Role name, ID, or Role model instance
     * @return bool
     */
    public function hasRole(User $user, string|Role $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Check if a user has any of the given roles.
     *
     * @param User $user
     * @param array<string|Role> $roles Array of role names, IDs, or Role model instances
     * @return bool
     */
    public function hasAnyRole(User $user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    /**
     * Check if a user has all of the given roles.
     *
     * @param User $user
     * @param array<string|Role> $roles Array of role names, IDs, or Role model instances
     * @return bool
     */
    public function hasAllRoles(User $user, array $roles): bool
    {
        return $user->hasAllRoles($roles);
    }

    /**
     * Get all permissions for a user (from roles + direct permissions).
     *
     * @param User $user
     * @return SupportCollection<int, Permission>
     */
    public function getUserPermissions(User $user): SupportCollection
    {
        return collect($user->getAllPermissions());
    }

    /**
     * Get all permissions for a specific guard.
     *
     * @param string $guardName Guard name (default: 'web')
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(string $guardName = 'web'): Collection
    {
        return Permission::where('guard_name', $guardName)->get();
    }

    /**
     * Get all roles for a user.
     *
     * @param User $user
     * @return Collection<int, Role>
     */
    public function getUserRoles(User $user): Collection
    {
        return $user->roles;
    }

    /**
     * Remove a role from a user.
     *
     * After removal, permissions will be re-synced automatically.
     *
     * @param User $user
     * @param string|Role $role Role name, ID, or Role model instance
     * @return void
     */
    public function removeRole(User $user, string|Role $role): void
    {
        $this->transaction(function () use ($user, $role) {
            $user->removeRole($role);
            // Re-sync permissions after role removal
            $this->syncUserPermissions($user);
        });
    }

    /**
     * Remove a permission from a user (direct permission only).
     *
     * This only removes direct permissions, not permissions from roles.
     *
     * @param User $user
     * @param string|Permission $permission Permission name, ID, or Permission model instance
     * @return void
     */
    public function removePermission(User $user, string|Permission $permission): void
    {
        $user->revokePermissionTo($permission);
    }

    /**
     * Remove all roles and permissions from a user.
     *
     * @param User $user
     * @return void
     */
    public function clearRolesAndPermissions(User $user): void
    {
        $this->transaction(function () use ($user) {
            $user->syncRoles([]);
            $user->syncPermissions([]);
        });
    }

    /**
     * Get all roles for a specific guard.
     *
     * @param string $guardName Guard name (default: 'web')
     * @return Collection<int, Role>
     */
    public function getAllRoles(string $guardName = 'web'): Collection
    {
        return Role::where('guard_name', $guardName)->get();
    }
}
