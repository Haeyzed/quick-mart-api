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

/**
 * Class DepartmentService
 *
 * Handles all core business logic and database interactions for Departments.
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
     * @param  UploadService  $uploadService  Service responsible for handling file uploads.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated departments based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
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
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Department The newly created Department model instance.
     */
    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            return Department::query()->create($data);
        });
    }

    /**
     * Update an existing department's information.
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
     * Delete a department.
     *
     * @param  Department  $department
     * @return void
     */
    public function deleteDepartment(Department $department): void
    {
        DB::transaction(function () use ($department) {
            $department->delete();
        });
    }

    /**
     * Bulk delete multiple departments.
     *
     * @param  array<int>  $ids  Array of department IDs to be deleted.
     * @return int The total count of successfully deleted departments.
     */
    public function bulkDeleteDepartments(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Department::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Update the active status for multiple departments.
     *
     * @param  array<int>  $ids  Array of department IDs to update.
     * @param  bool  $isActive  The new active status.
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Department::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple departments from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     * @return void
     */
    public function importDepartments(UploadedFile $file): void
    {
        ExcelFacade::import(new DepartmentsImport, $file);
    }

    /**
     * Download a departments CSV template.
     *
     * @return string The absolute path to the downloaded file.
     * @throws RuntimeException If the template file is missing.
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
     * Generate an export file containing department data.
     *
     * @param  array<int>  $ids  Specific department IDs to export.
     * @param  string  $format  The file format requested (excel/pdf).
     * @param  array<string>  $columns  Specific column names to include.
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
