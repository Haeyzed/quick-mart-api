<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OvertimeStatusEnum;
use App\Exports\OvertimesExport;
use App\Imports\OvertimesImport;
use App\Models\Overtime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class OvertimeService
 *
 * Handles all core business logic and database interactions for Overtimes.
 * Acts as the intermediary between the controllers and the database layer.
 */
class OvertimeService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * OvertimeService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated overtimes based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedOvertimes(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Overtime::query()
            ->with(['employee', 'approver'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a newly registered overtime request.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Overtime The newly created Overtime model instance.
     */
    public function createOvertime(array $data): Overtime
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? OvertimeStatusEnum::PENDING->value;
            $data['approved_by'] = Auth::id();

            return Overtime::query()->create($data);
        });
    }

    /**
     * Update an existing overtime request.
     *
     * @param  Overtime  $overtime  The overtime model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Overtime The freshly updated Overtime model instance.
     */
    public function updateOvertime(Overtime $overtime, array $data): Overtime
    {
        return DB::transaction(function () use ($overtime, $data) {
            if (isset($data['status'])) {
                $data['approved_by'] = Auth::id();
            }

            $overtime->update($data);

            return $overtime->fresh(['employee', 'approver']);
        });
    }

    /**
     * Delete an overtime request.
     *
     * @param  Overtime  $overtime
     * @return void
     */
    public function deleteOvertime(Overtime $overtime): void
    {
        DB::transaction(function () use ($overtime) {
            $overtime->delete();
        });
    }

    /**
     * Bulk delete multiple overtime requests.
     *
     * @param  array<int>  $ids  Array of overtime IDs to be deleted.
     * @return int The total count of successfully deleted overtime requests.
     */
    public function bulkDeleteOvertimes(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Overtime::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Update the status for multiple overtime requests.
     *
     * @param  array<int>  $ids  Array of overtime IDs to update.
     * @param  string  $status  The new status (Approved/Rejected/Pending).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Overtime::query()->whereIn('id', $ids)->update([
            'status' => $status->value,
            'approved_by' => Auth::id()
        ]);
    }

    /**
     * Import multiple overtime requests from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     * @return void
     */
    public function importOvertimes(UploadedFile $file): void
    {
        ExcelFacade::import(new OvertimesImport, $file);
    }

    /**
     * Download an overtimes CSV template.
     *
     * @return string The absolute path to the downloaded file.
     * @throws RuntimeException If the template file is missing.
     */
    public function download(): string
    {
        $fileName = 'overtimes-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template overtimes not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing overtime data.
     *
     * @param  array<int>  $ids  Specific overtime IDs to export.
     * @param  string  $format  The file format requested (excel/pdf).
     * @param  array<string>  $columns  Specific column names to include.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'overtimes_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new OvertimesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
