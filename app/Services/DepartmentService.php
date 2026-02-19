<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\DepartmentsExport;
use App\Imports\DepartmentsImport;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class DepartmentService
 * Handles business logic for Departments.
 */
class DepartmentService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated departments based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedDepartments(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Department::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of department options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Department::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn(Department $department) => [
                'value' => $department->id,
                'label' => $department->name,
            ]);
    }

    /**
     * Create a new department.
     *
     * @param array<string, mixed> $data
     */
    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            $data['is_active'] = filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
            return Department::query()->create($data);
        });
    }

    /**
     * Update an existing department.
     *
     * @param array<string, mixed> $data
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            $department->update($data);
            return $department->fresh();
        });
    }

    /**
     * Delete a department.
     *
     * @throws ConflictHttpException
     */
    public function deleteDepartment(Department $department): void
    {
        if ($department->employees()->exists()) {
            throw new ConflictHttpException("Cannot delete department '{$department->name}' as it has associated employees.");
        }

        DB::transaction(function () use ($department) {
            $department->delete();
        });
    }

    /**
     * Bulk delete departments.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteDepartments(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $departments = Department::query()->whereIn('id', $ids)->withCount('employees')->get();
            $count = 0;

            foreach ($departments as $department) {
                if ($department->employees_count > 0) {
                    continue;
                }

                $department->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update status for multiple departments.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Department::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import departments from file.
     */
    public function importDepartments(UploadedFile $file): void
    {
        ExcelFacade::import(new DepartmentsImport, $file);
    }

    /**
     * Download a departments CSV template.
     */
    public function download(): string
    {
        $fileName = "departments-sample.csv";

        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException("Template departments not found.");
        }

        return $path;
    }

    /**
     * Export departments to file.
     *
     * @param array<int> $ids
     * @param string $format 'excel' or 'pdf'
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters Optional date filters for created_at
     * @return string Relative file path.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'departments_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new DepartmentsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
