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
 * Handles all core business logic for assigning, syncing, and resolving
 * roles and permissions for users. Acts as a centralized service to interact
 * with the underlying Spatie Laravel-Permission package while gracefully handling
 * guard discrepancies (e.g., 'web' vs 'sanctum' API requests).
 */
class UserRolePermissionService extends BaseService
{
    /**
     * Assign roles and direct permissions to a user with automatic deduplication.
     * Wraps the operation in a database transaction to ensure data integrity.
     *
     * @param User $user The user model to update.
     * @param array<int|string|Role>|null $roles An array of Role IDs, names, or models.
     * @param array<int|string|Permission>|null $directPermissions An array of Permission IDs, names, or models.
     * @return void
     */
    public function assignRolesAndPermissions(
        User   $user,
        ?array $roles = null,
        ?array $directPermissions = null
    ): void {
        $this->transaction(function () use ($user, $roles, $directPermissions) {
            if ($roles !== null && !empty($roles)) {
                $this->assignRoles($user, $roles);
            }

            $this->syncUserPermissions($user, $directPermissions);
        });
    }

    /**
     * Assign roles to a user.
     * Translates mixed inputs (IDs, strings, models) into valid Role models before syncing.
     *
     * @param User $user The user model.
     * @param array<int|string|Role> $roles The roles to assign.
     * @return void
     */
    public function assignRoles(User $user, array $roles): void
    {
        $roleModels = $this->normalizeRoles($roles);
        $user->syncRoles($roleModels);
    }

    /**
     * Normalize a mixed array of roles into an array of Role models.
     * Bypasses Spatie's strict guard checking to prevent API (sanctum) vs Web guard mismatch errors.
     *
     * @param array<int|string|Role> $roles
     * @return array<int, Role>
     */
    private function normalizeRoles(array $roles): array
    {
        $normalized = [];

        foreach ($roles as $role) {
            if ($role instanceof Role) {
                $normalized[] = $role;
            } elseif (is_numeric($role)) {
                // Bypass strict guard checking using Eloquent find()
                $roleModel = Role::find($role);
                if ($roleModel) {
                    $normalized[] = $roleModel;
                }
            } elseif (is_string($role)) {
                // Bypass Spatie's findByName to allow cross-guard retrieval
                $roleModel = Role::where('name', $role)
                    ->whereIn('guard_name', ['web', 'sanctum'])
                    ->first();

                if ($roleModel) {
                    $normalized[] = $roleModel;
                }
            }
        }

        return $normalized;
    }

    /**
     * Sync a user's direct permissions alongside the permissions they inherit from roles.
     * Ensures there are no duplicate permissions assigned.
     *
     * @param User $user The user model.
     * @param array<int|string|Permission>|null $directPermissions The direct permissions to sync.
     * @return void
     */
    public function syncUserPermissions(User $user, ?array $directPermissions = null): void
    {
        $rolePermissions = $this->getPermissionsFromRoles($user->roles);

        $directPermissionModels = $directPermissions !== null && !empty($directPermissions)
            ? $this->normalizePermissions($directPermissions)
            : collect([]);

        $allPermissions = $rolePermissions->merge($directPermissionModels)->unique('id')->values();

        $user->syncPermissions($allPermissions);
    }

    /**
     * Extract and flatten all permissions associated with a given collection of roles.
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

        return $permissions->unique('id')->values();
    }

    /**
     * Normalize a mixed array of permissions into a Collection of Permission models.
     * Bypasses Spatie's strict guard checking to prevent API (sanctum) vs Web guard mismatch errors.
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
                // Bypass strict guard checking using Eloquent find()
                $model = Permission::find($permission);
                if ($model) {
                    $normalized->push($model);
                }
            } elseif (is_string($permission)) {
                // Bypass Spatie's findByName to allow cross-guard retrieval
                $model = Permission::where('name', $permission)
                    ->whereIn('guard_name', ['web', 'sanctum'])
                    ->first();

                if ($model) {
                    $normalized->push($model);
                }
            }
        }

        return $normalized;
    }

    /**
     * Check if a user has a specific permission.
     *
     * @param User $user
     * @param string $permission
     * @return bool
     */
    public function checkPermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if a user has at least one of the specified permissions.
     *
     * @param User $user
     * @param array<string> $permissions
     * @return bool
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if a user has all of the specified permissions.
     *
     * @param User $user
     * @param array<string> $permissions
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
     * @param string|Role $role
     * @return bool
     */
    public function hasRole(User $user, string|Role $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Check if a user has at least one of the specified roles.
     *
     * @param User $user
     * @param array<string|Role> $roles
     * @return bool
     */
    public function hasAnyRole(User $user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    /**
     * Check if a user has all of the specified roles.
     *
     * @param User $user
     * @param array<string|Role> $roles
     * @return bool
     */
    public function hasAllRoles(User $user, array $roles): bool
    {
        return $user->hasAllRoles($roles);
    }

    /**
     * Retrieve all permissions (direct and inherited) for a given user.
     *
     * @param User $user
     * @return SupportCollection<int, Permission>
     */
    public function getUserPermissions(User $user): SupportCollection
    {
        return collect($user->getAllPermissions());
    }

    /**
     * Retrieve all available permissions for a specific guard.
     *
     * @param string $guardName Defaults to 'web'.
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(string $guardName = 'web'): Collection
    {
        return Permission::where('guard_name', $guardName)->get();
    }

    /**
     * Retrieve all roles assigned to a user.
     *
     * @param User $user
     * @return Collection<int, Role>
     */
    public function getUserRoles(User $user): Collection
    {
        return $user->roles;
    }

    /**
     * Retrieve all available roles for a specific guard.
     *
     * @param string $guardName Defaults to 'web'.
     * @return Collection<int, Role>
     */
    public function getAllRoles(string $guardName = 'web'): Collection
    {
        return Role::where('guard_name', $guardName)->get();
    }

    /**
     * Remove a specific role from a user and synchronize their permissions.
     *
     * @param User $user
     * @param string|Role $role
     * @return void
     */
    public function removeRole(User $user, string|Role $role): void
    {
        $this->transaction(function () use ($user, $role) {
            $user->removeRole($role);
            $this->syncUserPermissions($user);
        });
    }

    /**
     * Revoke a specific direct permission from a user.
     *
     * @param User $user
     * @param string|Permission $permission
     * @return void
     */
    public function removePermission(User $user, string|Permission $permission): void
    {
        $user->revokePermissionTo($permission);
    }

    /**
     * Completely clear all roles and direct permissions from a user.
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
}
