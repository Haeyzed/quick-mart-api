<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * UserService
 *
 * Handles all business logic for user operations, including role and permission management.
 * Follows the same structure as CustomerService: no permission checks in service (controller handles auth).
 */
class UserService extends BaseService
{
    public function __construct(
        private readonly UserRolePermissionService $userRolePermissionService
    ) {}

    /**
     * Get list of all active users (id, name, email).
     *
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Retrieve a single user by instance.
     */
    public function getUser(User $user): User
    {
        return $user->fresh(['roles', 'permissions']);
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        $user = User::create($data);

        if (! empty($data['roles']) || ! empty($data['permissions'])) {
            $this->userRolePermissionService->assignRolesAndPermissions(
                $user,
                $data['roles'] ?? null,
                $data['permissions'] ?? null
            );
        }

        return $user->fresh(['roles', 'permissions']);
    }

    /**
     * Update an existing user.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data): User
    {
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        if (array_key_exists('roles', $data) || array_key_exists('permissions', $data)) {
            $this->userRolePermissionService->assignRolesAndPermissions(
                $user,
                $data['roles'] ?? null,
                $data['permissions'] ?? null
            );
        }

        return $user->fresh(['roles', 'permissions']);
    }

    /**
     * Delete a user (soft delete or hard delete depending on User model).
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
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
        User $user,
        ?array $roles = null,
        ?array $directPermissions = null
    ): void {
        $this->userRolePermissionService->assignRolesAndPermissions($user, $roles, $directPermissions);
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
        $this->userRolePermissionService->syncUserPermissions($user, $directPermissions);
    }

    /**
     * Get all permissions for a user.
     *
     * @param User $user
     * @return SupportCollection<int, Permission>
     */
    public function getUserPermissions(User $user): SupportCollection
    {
        return $this->userRolePermissionService->getUserPermissions($user);
    }

    /**
     * Get all roles for a user.
     *
     * @param User $user
     * @return Collection<int, Role>
     */
    public function getUserRoles(User $user): Collection
    {
        return $this->userRolePermissionService->getUserRoles($user);
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
        return $this->userRolePermissionService->checkPermission($user, $permission);
    }
}

