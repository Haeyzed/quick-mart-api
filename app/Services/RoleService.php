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
 * Handles business logic for Roles.
 */
class RoleService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated roles based on filters.
     *
     * @param array<string, mixed> $filters
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
     * Get list of role options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Role::active()
            ->where('guard_name', 'web')
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'value' => $role->id,
                'label' => $role->name,
            ]);
    }

    /**
     * Create a new role.
     *
     * @param array<string, mixed> $data
     */
    public function createRole(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $data['guard_name'] = $data['guard_name'] ?? 'web';
            $role = Role::query()->create($data);
            if (! empty($data['permission_ids'])) {
                $role->syncPermissions($data['permission_ids']);
            }
            return $role;
        });
    }

    /**
     * Update an existing role.
     *
     * @param array<string, mixed> $data
     */
    public function updateRole(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update($data);
            if (array_key_exists('permission_ids', $data)) {
                $role->syncPermissions($data['permission_ids'] ?? []);
            }
            return $role->fresh();
        });
    }

    /**
     * Delete a role.
     */
    public function deleteRole(Role $role): void
    {
        DB::transaction(function () use ($role) {
            $role->delete();
        });
    }

    /**
     * Bulk delete roles.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteRoles(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $roles = Role::query()->whereIn('id', $ids)->get();
            $count = 0;
            foreach ($roles as $role) {
                $role->delete();
                $count++;
            }
            return $count;
        });
    }

    /**
     * Update status for multiple roles.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Role::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import roles from file.
     */
    public function importRoles(UploadedFile $file): void
    {
        ExcelFacade::import(new RolesImport, $file);
    }

    /**
     * Download roles CSV template.
     */
    public function download(): string
    {
        $fileName = 'roles-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);
        if (! File::exists($path)) {
            throw new RuntimeException('Roles import template not found.');
        }
        return $path;
    }

    /**
     * Generate roles export file.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string, mixed> $filters
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
