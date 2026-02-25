<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use App\Models\Employee;
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
 * Class EmployeeService
 *
 * Handles all core business logic and database interactions for Employees.
 * Acts as the intermediary between the controllers and the database layer.
 */
class EmployeeService
{
    /**
     * The application path where employee images are stored.
     */
    private const IMAGE_PATH = 'images/employees';

    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * EmployeeService constructor.
     *
     * @param UploadService $uploadService Service responsible for handling file uploads.
     */
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    /**
     * Get paginated employees based on filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedEmployees(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Employee::query()
            ->with([
                'department:id,name',
                'designation:id,name',
                'shift:id,name',
                'country:id,name',
                'state:id,name',
                'city:id,name',
            ])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of employee options.
     * Returns value/label format for select/combobox components.
     *
     * @param int|null $warehouseId
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(?int $warehouseId): Collection
    {
        return Employee::active()
            ->select('id', 'name', 'staff_id')
            ->when($warehouseId && $warehouseId != 0, fn($query) => $query->whereHas('user', fn($q) => $q->where('warehouse_id', $warehouseId)))
            ->orderBy('name')
            ->get()
            ->map(fn(Employee $employee) => [
                'value' => $employee->id,
                'label' => "{$employee->name} ({$employee->staff_id})",
            ]);
    }

    /**
     * Create a newly registered employee.
     *
     * @param array<string, mixed> $data
     * @return Employee The newly created Employee model instance.
     */
    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);

            return Employee::query()->create($data);
        });
    }

    /**
     * Handle Image Upload via UploadService.
     *
     * @param array<string, mixed> $data
     * @param Employee|null $employee
     * @return array<string, mixed>
     */
    private function handleUploads(array $data, ?Employee $employee = null): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($employee?->image) {
                $this->uploadService->delete($employee->image);
            }
            $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    /**
     * Update an existing employee.
     *
     * @param Employee $employee The employee model instance to update.
     * @param array<string, mixed> $data
     * @return Employee The freshly updated Employee model instance.
     */
    public function updateEmployee(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $data = $this->handleUploads($data, $employee);
            $employee->update($data);

            return $employee->fresh();
        });
    }

    /**
     * Delete an employee.
     *
     * @param Employee $employee
     * @return void
     * @throws ConflictHttpException If the employee is linked to existing payrolls or attendances.
     */
    public function deleteEmployee(Employee $employee): void
    {
        if ($employee->payrolls()->exists() || $employee->attendances()->exists()) {
            throw new ConflictHttpException("Cannot delete employee '{$employee->name}' as they have associated HR records.");
        }

        DB::transaction(function () use ($employee) {
            $this->cleanupFiles($employee);
            $employee->delete();
        });
    }

    /**
     * Remove associated files.
     *
     * @param Employee $employee
     * @return void
     */
    private function cleanupFiles(Employee $employee): void
    {
        if ($employee->image) {
            $this->uploadService->delete($employee->image);
        }
    }

    /**
     * Bulk delete multiple employee records.
     *
     * @param array<int> $ids Array of employee IDs to be deleted.
     * @return int The total count of successfully deleted employee records.
     */
    public function bulkDeleteEmployees(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $employees = Employee::query()->whereIn('id', $ids)->withCount(['payrolls', 'attendances'])->get();
            $count = 0;

            foreach ($employees as $employee) {
                if ($employee->payrolls_count > 0 || $employee->attendances_count > 0) {
                    continue;
                }

                $this->cleanupFiles($employee);
                $employee->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the status for multiple employee records.
     *
     * @param array<int> $ids Array of employee IDs to update.
     * @param bool $isActive The new status value.
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Employee::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple employee records from an uploaded file.
     *
     * @param UploadedFile $file The uploaded spreadsheet file.
     * @return void
     */
    public function importEmployees(UploadedFile $file): void
    {
        ExcelFacade::import(new EmployeesImport, $file);
    }

    /**
     * Download an employees CSV template.
     *
     * @return string The absolute path to the downloaded file.
     * @throws RuntimeException If the template file is missing.
     */
    public function download(): string
    {
        $fileName = 'employees-sample.csv';
        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException('Template employees not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing employee data.
     *
     * @param array<int> $ids Specific employee IDs to export.
     * @param string $format The file format requested (excel/pdf).
     * @param array<string> $columns Specific column names to include.
     * @param array{start_date?: string, end_date?: string} $filters Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'employees_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new EmployeesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
