<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Staff Access Trait
 *
 * Provides methods to filter queries based on staff access permissions.
 * This trait enforces access control for staff members based on their role
 * and the configured access level (own records or warehouse-based).
 *
 * @package App\Traits
 */
trait StaffAccess
{
    /**
     * Apply staff access restrictions to a query builder.
     *
     * This method filters the query based on:
     * - Staff: user does not have a full-access role (see config permission.full_access_roles)
     * - Access configuration ('own' or 'warehouse')
     *
     * If the user is staff:
     * - 'own' access: Only show records created by the user
     * - 'warehouse' access: Only show records for the user's warehouse
     *
     * @param Builder $query The query builder instance to filter
     * @return void
     */
    public function staffAccessCheck(Builder $query): void
    {
        $user = Auth::user();

        if (!$user || !$user->isStaff()) {
            // Full-access role, no restrictions
            return;
        }

        $staffAccess = config('staff_access', 'own');

        if ($staffAccess === 'own') {
            $query->where('user_id', $user->id);
        } elseif ($staffAccess === 'warehouse' && isset($user->warehouse_id)) {
            $query->where('warehouse_id', $user->warehouse_id);
        }
    }
}

