<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserIndexRequest;
use App\Http\Requests\Users\UserRequest;
use App\Models\User;
use App\Services\UserService;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Http\JsonResponse;

/**
 * UserController
 *
 * Handles user-related API requests.
 */
class UserController extends Controller
{
    use CheckPermissionsTrait;

    public function __construct(
        private readonly UserService $service
    )
    {
    }

    /**
     * Get list of users.
     *
     * @param UserIndexRequest $request
     * @return JsonResponse
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        // Permission check is handled in UserService::getUsers()
        $users = $this->service->getUsers();
        return response()->success($users, 'Users fetched successfully');
    }

    /**
     * Get a single user by ID.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        // Check permission: user needs 'users-index' permission to view users
        $this->requirePermission('users-index');

        return response()->success($user->load('roles', 'permissions'), 'User fetched successfully');
    }

    /**
     * Create a new user.
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        // Check permission: user needs 'users-add' permission to create users
        $this->requirePermission('users-add');

        $user = User::create($request->validated());

        // Assign roles and permissions if provided
        if ($request->has('roles') || $request->has('permissions')) {
            $this->service->assignRolesAndPermissions(
                $user,
                $request->input('roles'),
                $request->input('permissions')
            );
        }

        return response()->success($user->load('roles', 'permissions'), 'User created successfully', 201);
    }

    /**
     * Update an existing user.
     *
     * @param UserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        // Check permission: user needs 'users-edit' permission to update users
        $this->requirePermission('users-edit');

        $data = $request->validated();

        // Remove password if not provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        // Assign roles and permissions if provided
        if ($request->has('roles') || $request->has('permissions')) {
            $this->service->assignRolesAndPermissions(
                $user,
                $request->input('roles'),
                $request->input('permissions')
            );
        }

        return response()->success($user->fresh()->load('roles', 'permissions'), 'User updated successfully');
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        // Check permission: user needs 'users-delete' permission to delete users
        $this->requirePermission('users-delete');

        $user->delete();

        return response()->success(null, 'User deleted successfully');
    }
}

