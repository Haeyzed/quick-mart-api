<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\ShiftsExport;
use App\Imports\ShiftsImport;
use App\Models\Shift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class ShiftService
 *
 * Handles all core business logic and database interactions for Shifts.
 * Acts as the intermediary between the controllers and the database layer.
 */
class ShiftService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * ShiftService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated shifts based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedShifts(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Shift::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active shift options.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return Shift::active()
            ->select('id', 'name', 'start_time', 'end_time')
            ->orderBy('name')
            ->get()
            ->map(fn (Shift $shift) => [
                'value' => $shift->id,
                'label' => "{$shift->name} ({$shift->start_time} - {$shift->end_time})",
            ]);
    }

    /**
     * Create a newly registered shift.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Shift The newly created Shift model instance.
     */
    public function createShift(array $data): Shift
    {
        return DB::transaction(function () use ($data) {
            return Shift::query()->create($data);
        });
    }

    /**
     * Update an existing shift's information.
     *
     * @param  Shift  $shift  The shift model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Shift The freshly updated Shift model instance.
     */
    public function updateShift(Shift $shift, array $data): Shift
    {
        return DB::transaction(function () use ($shift, $data) {
            $shift->update($data);

            return $shift->fresh();
        });
    }

    /**
     * Delete a shift.
     *
     * @param  Shift  $shift
     * @return void
     */
    public function deleteShift(Shift $shift): void
    {
        DB::transaction(function () use ($shift) {
            $shift->delete();
        });
    }

    /**
     * Bulk delete multiple shifts.
     *
     * @param  array<int>  $ids  Array of shift IDs to be deleted.
     * @return int The total count of successfully deleted shifts.
     */
    public function bulkDeleteShifts(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Shift::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Update the active status for multiple shifts.
     *
     * @param  array<int>  $ids  Array of shift IDs to update.
     * @param  bool  $isActive  The new active status.
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Shift::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple shifts from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     * @return void
     */
    public function importShifts(UploadedFile $file): void
    {
        ExcelFacade::import(new ShiftsImport, $file);
    }

    /**
     * Download a shifts CSV template.
     *
     * @return string The absolute path to the downloaded file.
     * @throws RuntimeException If the template file is missing.
     */
    public function download(): string
    {
        $fileName = 'shifts-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template shifts not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing shift data.
     *
     * @param  array<int>  $ids  Specific shift IDs to export.
     * @param  string  $format  The file format requested (excel/pdf).
     * @param  array<string>  $columns  Specific column names to include.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'shifts_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new ShiftsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
