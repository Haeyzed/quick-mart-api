<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\PermissionsExport;
use App\Imports\PermissionsImport;
use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class PermissionService
 * Handles business logic for Permissions.
 */
class PermissionService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated permissions based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedPermissions(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Permission::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of permission options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Permission::active()
            ->where('guard_name', 'web')
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $permission) => [
                'value' => $permission->id,
                'label' => $permission->name,
            ]);
    }

    /**
     * Create a new permission.
     *
     * @param array<string, mixed> $data
     */
    public function createPermission(array $data): Permission
    {
        return DB::transaction(function () use ($data) {
            $data['guard_name'] = $data['guard_name'] ?? 'web';
            return Permission::query()->create($data);
        });
    }

    /**
     * Update an existing permission.
     *
     * @param array<string, mixed> $data
     */
    public function updatePermission(Permission $permission, array $data): Permission
    {
        return DB::transaction(function () use ($permission, $data) {
            $permission->update($data);
            return $permission->fresh();
        });
    }

    /**
     * Delete a permission.
     */
    public function deletePermission(Permission $permission): void
    {
        DB::transaction(function () use ($permission) {
            $permission->delete();
        });
    }

    /**
     * Bulk delete permissions.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
     */
    public function bulkDeletePermissions(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $permissions = Permission::query()->whereIn('id', $ids)->get();
            $count = 0;
            foreach ($permissions as $permission) {
                $permission->delete();
                $count++;
            }
            return $count;
        });
    }

    /**
     * Update status for multiple permissions.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Permission::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import permissions from file.
     */
    public function importPermissions(UploadedFile $file): void
    {
        ExcelFacade::import(new PermissionsImport, $file);
    }

    /**
     * Download permissions CSV template.
     */
    public function download(): string
    {
        $fileName = 'permissions-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);
        if (! File::exists($path)) {
            throw new RuntimeException('Permissions import template not found.');
        }
        return $path;
    }

    /**
     * Generate permissions export file.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'permissions_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;
        ExcelFacade::store(
            new PermissionsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );
        return $relativePath;
    }
}
