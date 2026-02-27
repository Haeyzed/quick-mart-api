<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeaveStatusEnum;
use App\Exports\LeavesExport;
use App\Imports\LeavesImport;
use App\Models\Leave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class LeaveService
 *
 * Handles all core business logic and database interactions for Leaves.
 * Acts as the intermediary between the controllers and the database layer.
 */
class LeaveService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * LeaveService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated leaves based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedLeaves(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Leave::query()
            ->with(['employee', 'leaveType', 'approver'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a newly registered leave request.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Leave The newly created Leave model instance.
     */
    public function createLeave(array $data): Leave
    {
        return DB::transaction(function () use ($data) {
            $data['days'] = $this->calculateDays($data['start_date'], $data['end_date']);
            $data['status'] = LeaveStatusEnum::PENDING->value;
            $data['approver_id'] = Auth::id();

            return Leave::query()->create($data);
        });
    }

    /**
     * Update an existing leave request.
     *
     * @param  Leave  $leave  The leave model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Leave The freshly updated Leave model instance.
     */
    public function updateLeave(Leave $leave, array $data): Leave
    {
        return DB::transaction(function () use ($leave, $data) {
            if (isset($data['start_date']) && isset($data['end_date'])) {
                $data['days'] = $this->calculateDays($data['start_date'], $data['end_date']);
            }
            if (isset($data['status'])) {
                $data['approver_id'] = Auth::id();
            }

            $leave->update($data);

            return $leave->fresh(['employee', 'leaveType']);
        });
    }

    /**
     * Delete a leave request.
     */
    public function deleteLeave(Leave $leave): void
    {
        DB::transaction(function () use ($leave) {
            $leave->delete();
        });
    }

    /**
     * Bulk delete multiple leave requests.
     *
     * @param  array<int>  $ids  Array of leave IDs to be deleted.
     * @return int The total count of successfully deleted leave requests.
     */
    public function bulkDeleteLeaves(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $leaves = Leave::query()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($leaves as $leave) {
                $leave->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the status for multiple leave requests.
     *
     * @param  array<int>  $ids  Array of leave IDs to update.
     * @param  string  $status  The new status (Approved/Rejected/Pending).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Leave::query()->whereIn('id', $ids)->update([
            'status' => $status,
            'approver_id' => Auth::id()
        ]);
    }

    /**
     * Import multiple leave requests from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     */
    public function importLeaves(UploadedFile $file): void
    {
        ExcelFacade::import(new LeavesImport, $file);
    }

    /**
     * Download a leaves CSV template.
     */
    public function download(): string
    {
        $fileName = 'leaves-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template leaves not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing leave data.
     *
     * @param  array<int>  $ids  Specific leave IDs to export.
     * @param  string  $format  The file format requested.
     * @param  array<string>  $columns  Specific column names to include.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'leaves_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new LeavesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }

    /**
     * Calculate the number of days between two dates inclusive.
     */
    private function calculateDays(string $start, string $end): float
    {
        return (strtotime($end) - strtotime($start)) / 86400 + 1;
    }
}
