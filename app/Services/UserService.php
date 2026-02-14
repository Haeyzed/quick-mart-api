<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * UserService
 *
 * Handles all business logic for user operations, including role and permission management.
 */
class UserService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * Permission service instance.
     *
     * @var PermissionService
     */
    protected PermissionService $permissionService;

    /**
     * UserService constructor.
     *
     * @param PermissionService $permissionService
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Get list of all active users.
     *
     * @return Collection<User>
     */
    public function getUsers(): Collection
    {
        // Check permission: user needs 'users-index' permission to view users
        //$this->requirePermission('users-index');

        return User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Assign roles and permissions to a user.
     *
     * This method handles:
     * - Assigning multiple roles
     * - Collecting permissions from roles
     * - Adding direct permissions
     * - Automatic deduplication
     *
     * @param User $user
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
        // Check permission: user needs 'users-edit' permission to assign roles/permissions
        $this->requirePermission('users-edit');

        $this->permissionService->assignRolesAndPermissions($user, $roles, $directPermissions);
    }

    /**
     * Sync user permissions (collects from roles and merges with direct permissions).
     *
     * @param User $user
     * @param array<int|string|Permission>|null $directPermissions Optional direct permissions to add
     * @return void
     */
    public function syncUserPermissions(User $user, ?array $directPermissions = null): void
    {
        $this->permissionService->syncUserPermissions($user, $directPermissions);
    }

    /**
     * Get all permissions for a user.
     *
     * @param User $user
     * @return SupportCollection<int, Permission>
     */
    public function getUserPermissions(User $user): SupportCollection
    {
        return $this->permissionService->getUserPermissions($user);
    }

    /**
     * Get all roles for a user.
     *
     * @param User $user
     * @return Collection<int, Role>
     */
    public function getUserRoles(User $user): Collection
    {
        return $this->permissionService->getUserRoles($user);
    }

    /**
     * Check if a user has a specific permission.
     *
     * @param User $user
     * @param string $permission Permission name
     * @return bool
     */
    public function checkPermission(User $user, string $permission): bool
    {
        return $this->permissionService->checkPermission($user, $permission);
    }
}

