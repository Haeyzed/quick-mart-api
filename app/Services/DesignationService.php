<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\DesignationsExport;
use App\Imports\DesignationsImport;
use App\Models\Designation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class DesignationService
 *
 * Handles all core business logic and database interactions for Designations.
 * Acts as the intermediary between the controllers and the database layer.
 */
class DesignationService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * DesignationService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated designations based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedDesignations(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Designation::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active designation options.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return Designation::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Designation $designation) => [
                'value' => $designation->id,
                'label' => $designation->name,
            ]);
    }

    /**
     * Create a newly registered designation.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Designation The newly created Designation model instance.
     */
    public function createDesignation(array $data): Designation
    {
        return DB::transaction(function () use ($data) {
            return Designation::query()->create($data);
        });
    }

    /**
     * Update an existing designation's information.
     *
     * @param  Designation  $designation  The designation model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Designation The freshly updated Designation model instance.
     */
    public function updateDesignation(Designation $designation, array $data): Designation
    {
        return DB::transaction(function () use ($designation, $data) {
            $designation->update($data);

            return $designation->fresh();
        });
    }

    /**
     * Delete a designation.
     *
     * @param  Designation  $designation
     * @return void
     */
    public function deleteDesignation(Designation $designation): void
    {
        DB::transaction(function () use ($designation) {
            $designation->delete();
        });
    }

    /**
     * Bulk delete multiple designations.
     *
     * @param  array<int>  $ids  Array of designation IDs to be deleted.
     * @return int The total count of successfully deleted designations.
     */
    public function bulkDeleteDesignations(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Designation::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Update the active status for multiple designations.
     *
     * @param  array<int>  $ids  Array of designation IDs to update.
     * @param  bool  $isActive  The new active status.
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Designation::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple designations from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     * @return void
     */
    public function importDesignations(UploadedFile $file): void
    {
        ExcelFacade::import(new DesignationsImport, $file);
    }

    /**
     * Download a designations CSV template.
     *
     * @return string The absolute path to the downloaded file.
     * @throws RuntimeException If the template file is missing.
     */
    public function download(): string
    {
        $fileName = 'designations-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template designations not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing designation data.
     *
     * @param  array<int>  $ids  Specific designation IDs to export.
     * @param  string  $format  The file format requested (excel/pdf).
     * @param  array<string>  $columns  Specific column names to include.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'designations_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new DesignationsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
