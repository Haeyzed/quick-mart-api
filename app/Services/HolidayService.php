<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\HolidaysExport;
use App\Imports\HolidaysImport;
use App\Models\Holiday;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class HolidayService
 *
 * Handles all core business logic and database interactions for Holidays.
 * Acts as the intermediary between the controllers and the database layer.
 */
class HolidayService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * HolidayService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated holidays based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedHolidays(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Holiday::query()
            ->with(['user'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a newly registered holiday.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Holiday The newly created Holiday model instance.
     */
    public function createHoliday(array $data): Holiday
    {
        return DB::transaction(function () use ($data) {
            $data['user_id'] = Auth::id();
            $data['is_approved'] = false;

            return Holiday::query()->create($data);
        });
    }

    /**
     * Update an existing holiday's information.
     *
     * @param  Holiday  $holiday  The holiday model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Holiday The freshly updated Holiday model instance.
     */
    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $holiday->update($data);

            return $holiday->fresh(['user']);
        });
    }

    /**
     * Delete a holiday.
     *
     * @param  Holiday  $holiday
     * @return void
     */
    public function deleteHoliday(Holiday $holiday): void
    {
        DB::transaction(function () use ($holiday) {
            $holiday->delete();
        });
    }

    /**
     * Bulk delete multiple holidays.
     *
     * @param  array<int>  $ids  Array of holiday IDs to be deleted.
     * @return int The total count of successfully deleted holidays.
     */
    public function bulkDeleteHolidays(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Holiday::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Update the approval status for multiple holidays.
     *
     * @param  array<int>  $ids  Array of holiday IDs to update.
     * @param  bool  $isApproved  The new approval status.
     * @return int The number of records updated.
     */
    public function bulkUpdateApproval(array $ids, bool $isApproved): int
    {
        return Holiday::query()->whereIn('id', $ids)->update(['is_approved' => $isApproved]);
    }

    /**
     * Import multiple holidays from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     * @return void
     */
    public function importHolidays(UploadedFile $file): void
    {
        ExcelFacade::import(new HolidaysImport, $file);
    }

    /**
     * Download a holidays CSV template.
     *
     * @return string The absolute path to the downloaded file.
     * @throws RuntimeException If the template file is missing.
     */
    public function download(): string
    {
        $fileName = 'holidays-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template holidays not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing holiday data.
     *
     * @param  array<int>  $ids  Specific holiday IDs to export.
     * @param  string  $format  The file format requested (excel/pdf).
     * @param  array<string>  $columns  Specific column names to include.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'holidays_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new HolidaysExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
