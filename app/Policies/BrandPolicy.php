<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of brands.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view brands');
    }

    /**
     * Determine whether the user can view a specific brand.
     */
    public function view(User $user, Brand $brand): bool
    {
        return $user->checkPermissionTo('view brand details');
    }

    /**
     * Determine whether the user can create brands.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create brands');
    }

    /**
     * Determine whether the user can update the brand.
     */
    public function update(User $user, Brand $brand): bool
    {
        return $user->checkPermissionTo('update brands');
    }

    /**
     * Determine whether the user can delete the brand.
     */
    public function delete(User $user, Brand $brand): bool
    {
        return $user->checkPermissionTo('delete brands');
    }

    /**
     * Custom: Determine whether the user can bulk delete brands.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete brands');
    }

    /**
     * Custom: Determine whether the user can bulk update status.
     */
    public function updateAny(User $user): bool
    {
        return $user->checkPermissionTo('update brands');
    }

    /**
     * Custom: Determine whether the user can import brands.
     */
    public function import(User $user): bool
    {
        return $user->checkPermissionTo('import brands');
    }

    /**
     * Custom: Determine whether the user can export brands.
     */
    public function export(User $user): bool
    {
        return $user->checkPermissionTo('export brands');
    }
}
