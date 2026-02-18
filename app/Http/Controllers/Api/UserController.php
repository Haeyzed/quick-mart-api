<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * UserController
 *
 * API controller for User CRUD. Follows the same structure as CustomerController:
 * permission checks in controller, Store/Update requests, delegate logic to UserService.
 *
 * @group User Management
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service
    ) {}

    /**
     * Display a listing of users (active, id, name, email).
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        if (auth()->user()->denies('view users')) {
            return response()->forbidden('Permission denied for viewing users list.');
        }

        $users = $this->service->getUsers();

        return response()->success($users, 'Users retrieved successfully');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        if (auth()->user()->denies('view users')) {
            return response()->forbidden('Permission denied for view user.');
        }

        $user = $this->service->getUser($user);

        return response()->success($user, 'User details retrieved successfully');
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create users')) {
            return response()->forbidden('Permission denied for create user.');
        }

        $user = $this->service->createUser($request->validated());

        return response()->success(
            $user,
            'User created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if (auth()->user()->denies('update users')) {
            return response()->forbidden('Permission denied for update user.');
        }

        $user = $this->service->updateUser($user, $request->validated());

        return response()->success($user, 'User updated successfully');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        if (auth()->user()->denies('delete users')) {
            return response()->forbidden('Permission denied for delete user.');
        }

        $this->service->deleteUser($user);

        return response()->success(null, 'User deleted successfully');
    }
}

