<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * UserService
 *
 * Handles all business logic for user operations.
 */
class UserService extends BaseService
{
    /**
     * Get list of all active users.
     *
     * @return Collection<User>
     */
    public function getUsers(): Collection
    {
        return User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}

