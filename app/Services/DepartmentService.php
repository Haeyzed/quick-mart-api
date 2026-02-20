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
 * * Handles all core business logic and database interactions for Departments.
 * Acts as the intermediary between the controllers and the database layer.
 */
class DepartmentService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * DepartmentService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated departments based on provided filters.
     *
     * Retrieves a paginated list of departments, applying scopes for searching,
     * status filtering, and date ranges.
     *
     * @param  array<string, mixed>  $filters  An associative array of filters (e.g., 'search', 'status', 'start_date', 'end_date').
     * @param  int  $perPage  The number of records to return per page.
     * @return LengthAwarePaginator A paginated collection of Department models.
     */
    public function getPaginatedDepartments(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Department::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active department options.
     *
     * Useful for populating frontend select dropdowns. Only fetches the `id` and `name` of active departments.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return Department::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Department $department) => [
                'value' => $department->id,
                'label' => $department->name,
            ]);
    }

    /**
     * Create a newly registered department.
     *
     * Stores the new department record within a database transaction to ensure data integrity.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new department.
     * @return Department The newly created Department model instance.
     */
    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            $data['is_active'] = filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return Department::query()->create($data);
        });
    }

    /**
     * Update an existing department's information.
     *
     * Updates the department record within a database transaction.
     *
     * @param  Department  $department  The department model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Department The freshly updated Department model instance.
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            $department->update($data);

            return $department->fresh();
        });
    }

    /**
     * Delete a specific department.
     *
     * Will abort if the department is currently linked to any existing employees.
     *
     * @param  Department  $department  The department model instance to delete.
     *
     * @throws ConflictHttpException If the department has associated employees.
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
     * Bulk delete multiple departments.
     *
     * Iterates over an array of department IDs and attempts to delete them.
     * Skips any departments that have associated employees to prevent database relationship errors.
     *
     * @param  array<int>  $ids  Array of department IDs to be deleted.
     * @return int The total count of successfully deleted departments.
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
     * Update the active status for multiple departments.
     *
     * @param  array<int>  $ids  Array of department IDs to update.
     * @param  bool  $isActive  The new active status (true for active, false for inactive).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Department::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple departments from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file containing department data.
     */
    public function importDepartments(UploadedFile $file): void
    {
        ExcelFacade::import(new DepartmentsImport, $file);
    }

    /**
     * Retrieve the path to the sample departments import template.
     *
     * @return string The absolute file path to the sample CSV.
     *
     * @throws RuntimeException If the template file does not exist on the server.
     */
    public function download(): string
    {
        $fileName = 'departments-sample.csv';

        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template departments not found.');
        }

        return $path;
    }

    /**
     * Generate an export file (Excel or PDF) containing department data.
     *
     * Compiles the requested department data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param  array<int>  $ids  Specific department IDs to export (leave empty to export all based on filters).
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include in the export.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'departments_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
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
