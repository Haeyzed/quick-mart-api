<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\LeaveTypesExport;
use App\Imports\LeaveTypesImport;
use App\Models\LeaveType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class LeaveTypeService
 *
 * Handles all core business logic and database interactions for Leave Types.
 * Acts as the intermediary between the controllers and the database layer.
 */
class LeaveTypeService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * LeaveTypeService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated leave types based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedLeaveTypes(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return LeaveType::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active leave type options.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return LeaveType::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (LeaveType $leaveType) => [
                'value' => $leaveType->id,
                'label' => $leaveType->name,
            ]);
    }

    /**
     * Create a newly registered leave type.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new leave type.
     * @return LeaveType The newly created LeaveType model instance.
     */
    public function createLeaveType(array $data): LeaveType
    {
        return DB::transaction(function () use ($data) {
            return LeaveType::query()->create($data);
        });
    }

    /**
     * Update an existing leave type's information.
     *
     * @param  LeaveType  $leaveType  The leave type model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return LeaveType The freshly updated LeaveType model instance.
     */
    public function updateLeaveType(LeaveType $leaveType, array $data): LeaveType
    {
        return DB::transaction(function () use ($leaveType, $data) {
            $leaveType->update($data);

            return $leaveType->fresh();
        });
    }

    /**
     * Delete a leave type.
     */
    public function deleteLeaveType(LeaveType $leaveType): void
    {
        DB::transaction(function () use ($leaveType) {
            $leaveType->delete();
        });
    }

    /**
     * Bulk delete multiple leave types.
     *
     * @param  array<int>  $ids  Array of leave type IDs to be deleted.
     * @return int The total count of successfully deleted leave types.
     */
    public function bulkDeleteLeaveTypes(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $leaveTypes = LeaveType::query()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($leaveTypes as $leaveType) {
                $leaveType->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple leave types.
     *
     * @param  array<int>  $ids  Array of leave type IDs to update.
     * @param  bool  $isActive  The new active status.
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return LeaveType::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple leave types from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     */
    public function importLeaveTypes(UploadedFile $file): void
    {
        ExcelFacade::import(new LeaveTypesImport, $file);
    }

    /**
     * Download a leave types CSV template.
     */
    public function download(): string
    {
        $fileName = 'leave-types-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template leave-types not found.');
        }

        return $path;
    }

    /**
     * Generate an export file (Excel or PDF) containing leave type data.
     *
     * @param  array<int>  $ids  Specific leave type IDs to export.
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'leave_types_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new LeaveTypesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
