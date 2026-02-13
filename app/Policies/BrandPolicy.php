<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BrandPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of brands.
     */
    public function viewAny(User $user): bool|Response
    {
        return $user->checkPermissionTo('view brands')
            ?: $this->deny('Permission denied for viewing brands list.');
    }

    /**
     * Determine whether the user can view a specific brand.
     */
    public function view(User $user, Brand $brand): bool|Response
    {
        return $user->checkPermissionTo('view brand details')
            ?: $this->deny('Permission denied for view brand.');
    }

    /**
     * Determine whether the user can create brands.
     */
    public function create(User $user): bool|Response
    {
        return $user->checkPermissionTo('create brands')
            ?: $this->deny('Permission denied for create brand.');
    }

    /**
     * Determine whether the user can update the brand.
     */
    public function update(User $user, Brand $brand): bool|Response
    {
        return $user->checkPermissionTo('update brands')
            ?: $this->deny('Permission denied for update brand.');
    }

    /**
     * Determine whether the user can delete the brand.
     */
    public function delete(User $user, Brand $brand): bool|Response
    {
        return $user->checkPermissionTo('delete brands')
            ?: $this->deny('Permission denied for delete brand.');
    }

    /**
     * Custom: Determine whether the user can bulk delete brands.
     */
    public function deleteAny(User $user): bool|Response
    {
        return $user->checkPermissionTo('delete brands')
            ?: $this->deny('Permission denied for bulk delete brands.');
    }

    /**
     * Custom: Determine whether the user can bulk update status.
     */
    public function updateAny(User $user): bool|Response
    {
        return $user->checkPermissionTo('update brands')
            ?: $this->deny('Permission denied for bulk update brands.');
    }

    /**
     * Custom: Determine whether the user can import brands.
     */
    public function import(User $user): bool|Response
    {
        return $user->checkPermissionTo('import brands')
            ?: $this->deny('Permission denied for import brands.');
    }

    /**
     * Custom: Determine whether the user can export brands.
     */
    public function export(User $user): bool|Response
    {
        return $user->checkPermissionTo('export brands')
            ?: $this->deny('Permission denied for export brands.');
    }
}
