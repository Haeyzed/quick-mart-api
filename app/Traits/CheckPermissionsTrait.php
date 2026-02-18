<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use App\Services\UserRolePermissionService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

/**
 * CheckPermissionsTrait
 *
 * Provides centralized permission checking methods for controllers and services.
 * Acts as a single source of truth for authorization decisions.
 */
trait CheckPermissionsTrait
{
    /**
     * Ensure the authenticated user has a specific permission.
     * Throws an exception if the user doesn't have the permission.
     *
     * @param string $permission Permission name
     * @param string|null $message Custom error message
     * @param int $statusCode HTTP status code (default: 403)
     * @return void
     * @throws HttpResponseException
     */
    protected function requirePermission(
        string  $permission,
        ?string $message = null,
        int     $statusCode = 403
    ): void
    {
        if (!$this->userHasPermission($permission)) {
            $message = $message ?? "You do not have permission to perform this action: {$permission}";
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => $message,
                    'permission' => $permission,
                ], $statusCode)
            );
        }
    }

    /**
     * Check if the authenticated user has a specific permission.
     *
     * @param string $permission Permission name
     * @param User|null $user Optional user to check (defaults to authenticated user)
     * @return bool
     */
    protected function userHasPermission(string $permission, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        return $this->getUserRolePermissionService()->checkPermission($user, $permission);
    }

    /**
     * Get the permission service instance.
     *
     * @return UserRolePermissionService
     */
    protected function getUserRolePermissionService(): UserRolePermissionService
    {
        return app(UserRolePermissionService::class);
    }

    /**
     * Ensure the authenticated user has any of the given permissions.
     * Throws an exception if the user doesn't have any of the permissions.
     *
     * @param array<string> $permissions Array of permission names
     * @param string|null $message Custom error message
     * @param int $statusCode HTTP status code (default: 403)
     * @return void
     * @throws HttpResponseException
     */
    protected function requireAnyPermission(
        array   $permissions,
        ?string $message = null,
        int     $statusCode = 403
    ): void
    {
        if (!$this->userHasAnyPermission($permissions)) {
            $message = $message ?? "You do not have any of the required permissions to perform this action.";
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => $message,
                    'required_permissions' => $permissions,
                ], $statusCode)
            );
        }
    }

    /**
     * Check if the authenticated user has any of the given permissions.
     *
     * @param array<string> $permissions Array of permission names
     * @param User|null $user Optional user to check (defaults to authenticated user)
     * @return bool
     */
    protected function userHasAnyPermission(array $permissions, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        return $this->getUserRolePermissionService()->hasAnyPermission($user, $permissions);
    }

    /**
     * Ensure the authenticated user has all of the given permissions.
     * Throws an exception if the user doesn't have all permissions.
     *
     * @param array<string> $permissions Array of permission names
     * @param string|null $message Custom error message
     * @param int $statusCode HTTP status code (default: 403)
     * @return void
     * @throws HttpResponseException
     */
    protected function requireAllPermissions(
        array   $permissions,
        ?string $message = null,
        int     $statusCode = 403
    ): void
    {
        if (!$this->userHasAllPermissions($permissions)) {
            $message = $message ?? "You do not have all of the required permissions to perform this action.";
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => $message,
                    'required_permissions' => $permissions,
                ], $statusCode)
            );
        }
    }

    /**
     * Check if the authenticated user has all of the given permissions.
     *
     * @param array<string> $permissions Array of permission names
     * @param User|null $user Optional user to check (defaults to authenticated user)
     * @return bool
     */
    protected function userHasAllPermissions(array $permissions, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        return $this->getUserRolePermissionService()->hasAllPermissions($user, $permissions);
    }

    /**
     * Ensure the authenticated user has a specific role.
     * Throws an exception if the user doesn't have the role.
     *
     * @param string $role Role name
     * @param string|null $message Custom error message
     * @param int $statusCode HTTP status code (default: 403)
     * @return void
     * @throws HttpResponseException
     */
    protected function requireRole(
        string  $role,
        ?string $message = null,
        int     $statusCode = 403
    ): void
    {
        if (!$this->userHasRole($role)) {
            $message = $message ?? "You do not have the required role to perform this action: {$role}";
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => $message,
                    'required_role' => $role,
                ], $statusCode)
            );
        }
    }

    /**
     * Check if the authenticated user has a specific role.
     *
     * @param string $role Role name
     * @param User|null $user Optional user to check (defaults to authenticated user)
     * @return bool
     */
    protected function userHasRole(string $role, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        return $this->getUserRolePermissionService()->hasRole($user, $role);
    }

    /**
     * Ensure the authenticated user has any of the given roles.
     * Throws an exception if the user doesn't have any of the roles.
     *
     * @param array<string> $roles Array of role names
     * @param string|null $message Custom error message
     * @param int $statusCode HTTP status code (default: 403)
     * @return void
     * @throws HttpResponseException
     */
    protected function requireAnyRole(
        array   $roles,
        ?string $message = null,
        int     $statusCode = 403
    ): void
    {
        if (!$this->userHasAnyRole($roles)) {
            $message = $message ?? "You do not have any of the required roles to perform this action.";
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => $message,
                    'required_roles' => $roles,
                ], $statusCode)
            );
        }
    }

    /**
     * Check if the authenticated user has any of the given roles.
     *
     * @param array<string> $roles Array of role names
     * @param User|null $user Optional user to check (defaults to authenticated user)
     * @return bool
     */
    protected function userHasAnyRole(array $roles, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        return $this->getUserRolePermissionService()->hasAnyRole($user, $roles);
    }
}
