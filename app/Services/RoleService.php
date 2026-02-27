<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\RolesExport;
use App\Imports\RolesImport;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class RoleService
 * * Handles all core business logic and database interactions for Roles.
 */
class RoleService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated roles based on filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedRoles(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Role::query()
            ->filter($filters)
            ->withCount('permissions')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a list of role options.
     *
     * @return Collection
     */
    public function getOptions(): Collection
    {
        return Role::active()
            ->where('guard_name', 'web')
            ->select('id', 'name', 'permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'value' => $role->id,
                'label' => $role->name,
            ]);
    }

    /**
     * Create a new role and optionally sync permissions.
     *
     * @param array<string, mixed> $data
     * @return Role
     */
    public function createRole(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $data['guard_name'] = $data['guard_name'] ?? 'web';
            $role = Role::create($data);

            if (!empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->load('permissions');
        });
    }

    /**
     * Update an existing role and sync its permissions.
     *
     * @param Role $role
     * @param array<string, mixed> $data
     * @return Role
     */
    public function updateRole(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update($data);

            if (array_key_exists('permissions', $data)) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->fresh('permissions');
        });
    }

    /**
     * Delete a role.
     *
     * @param Role $role
     * @return void
     */
    public function deleteRole(Role $role): void
    {
        DB::transaction(function () use ($role) {
            $role->delete();
        });
    }

    /**
     * Bulk delete multiple roles.
     *
     * @param array<int> $ids
     * @return int
     */
    public function bulkDeleteRoles(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Role::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Bulk update active status for roles.
     *
     * @param array<int> $ids
     * @param bool $isActive
     * @return int
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Role::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple roles from an uploaded file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importRoles(UploadedFile $file): void
    {
        ExcelFacade::import(new RolesImport, $file);
    }

    /**
     * Download a roles CSV template.
     *
     * @return string
     * @throws RuntimeException
     */
    public function download(): string
    {
        $fileName = 'roles-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (!File::exists($path)) {
            throw new RuntimeException('Roles import template not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing role data.
     *
     * @param array<int> $ids
     * @param string $format
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     * @return string
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'roles_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new RolesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
