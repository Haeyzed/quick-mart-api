<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\UnitsExport;
use App\Imports\UnitsImport;
use App\Models\Unit;
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
 * Class UnitService
 * * Handles all core business logic and database interactions for Units.
 * Acts as the intermediary between the controllers and the database layer.
 */
class UnitService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * UnitService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated units based on provided filters.
     *
     * Retrieves a paginated list of units, applying scopes for searching,
     * status filtering, and date ranges.
     *
     * @param  array<string, mixed>  $filters  An associative array of filters (e.g., 'search', 'status', 'start_date', 'end_date').
     * @param  int  $perPage  The number of records to return per page.
     * @return LengthAwarePaginator A paginated collection of Unit models.
     */
    public function getPaginatedUnits(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Unit::query()
            ->with('baseUnitRelation')
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active unit options.
     *
     * Useful for populating frontend select dropdowns. Only fetches the `id`, `name`, and `code` of active units.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name with code).
     */
    public function getOptions(): Collection
    {
        return Unit::active()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get()
            ->map(fn (Unit $unit) => [
                'value' => $unit->id,
                'label' => "{$unit->name} ({$unit->code})",
            ]);
    }

    /**
     * Get active base units (units with no base_unit).
     *
     * Useful for populating base-unit select dropdowns when creating or editing derived units.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name with code).
     */
    public function getBaseUnits(): Collection
    {
        return Unit::active()
            ->select('id', 'name', 'code')
            ->whereNull('base_unit')
            ->orderBy('name')
            ->get()
            ->map(fn (Unit $unit) => [
                'value' => $unit->id,
                'label' => "{$unit->name} ({$unit->code})",
            ]);
    }

    /**
     * Create a newly registered unit.
     *
     * Normalizes base_unit, operator, and operation_value when no base unit is provided.
     * Stores the new unit record within a database transaction to ensure data integrity.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new unit.
     * @return Unit The newly created Unit model instance.
     */
    public function createUnit(array $data): Unit
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['base_unit'])) {
                $data['operator'] = '*';
                $data['operation_value'] = 1;
                $data['base_unit'] = null;
            }

            return Unit::query()->create($data);
        });
    }

    /**
     * Update an existing unit's information.
     *
     * Normalizes base_unit, operator, and operation_value when no base unit is provided.
     * Updates the unit record within a database transaction.
     *
     * @param  Unit  $unit  The unit model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Unit The freshly updated Unit model instance.
     */
    public function updateUnit(Unit $unit, array $data): Unit
    {
        return DB::transaction(function () use ($unit, $data) {
            if (empty($data['base_unit'])) {
                $data['operator'] = '*';
                $data['operation_value'] = 1;
                $data['base_unit'] = null;
            }
            $unit->update($data);

            return $unit->fresh();
        });
    }

    /**
     * Delete a specific unit.
     *
     * Will abort if the unit is currently linked to any existing products or has sub-units.
     *
     * @param  Unit  $unit  The unit model instance to delete.
     *
     * @throws ConflictHttpException If the unit has associated products or sub-units.
     */
    public function deleteUnit(Unit $unit): void
    {
        if ($unit->products()->exists()) {
            throw new ConflictHttpException("Cannot delete unit '{$unit->name}' as it has associated products.");
        }

        if ($unit->subUnits()->exists()) {
            throw new ConflictHttpException("Cannot delete unit '{$unit->name}' as it has associated sub-units.");
        }

        DB::transaction(fn () => $unit->delete());
    }

    /**
     * Bulk delete multiple units.
     *
     * Iterates over an array of unit IDs and attempts to delete them.
     * Skips any units that have associated products or sub-units to prevent database relationship errors.
     *
     * @param  array<int>  $ids  Array of unit IDs to be deleted.
     * @return int The total count of successfully deleted units.
     */
    public function bulkDeleteUnits(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $units = Unit::query()->whereIn('id', $ids)->withCount(['products', 'subUnits'])->get();
            $count = 0;

            foreach ($units as $unit) {
                if ($unit->products_count > 0 || $unit->sub_units_count > 0) {
                    continue;
                }
                $unit->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple units.
     *
     * @param  array<int>  $ids  Array of unit IDs to update.
     * @param  bool  $isActive  The new active status (true for active, false for inactive).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Unit::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple units from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file containing unit data.
     */
    public function importUnits(UploadedFile $file): void
    {
        ExcelFacade::import(new UnitsImport, $file);
    }

    /**
     * Retrieve the path to the sample units import template.
     *
     * @return string The absolute file path to the sample CSV.
     *
     * @throws RuntimeException If the template file does not exist on the server.
     */
    public function download(): string
    {
        $fileName = 'units-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template units not found.');
        }

        return $path;
    }

    /**
     * Generate an export file (Excel or PDF) containing unit data.
     *
     * Compiles the requested unit data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param  array<int>  $ids  Specific unit IDs to export (leave empty to export all based on filters).
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include in the export.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'units_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new UnitsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
