<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * UserController
 *
 * Handles user-related API requests.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service
    ) {
    }

    /**
     * Get list of users.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = $this->service->getUsers();
        return response()->success($users, 'Users fetched successfully');
    }
}

