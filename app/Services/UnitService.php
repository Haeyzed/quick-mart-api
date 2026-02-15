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
 * Handles business logic for Units.
 */
class UnitService
{
    /**
     *
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * @param UploadService $uploadService
     */
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    /**
     * Get paginated units based on filters.
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
     * Get list of unit options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Unit::active()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get()
            ->map(fn(Unit $unit) => [
                'value' => $unit->id,
                'label' => "{$unit->name} ({$unit->code})",
            ]);
    }

    /**
     * Get active base units.
     */
    public function getBaseUnits(): Collection
    {
        return Unit::active()
            ->whereNull('base_unit')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new unit.
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
     * Update an existing unit.
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
     * Delete a unit.
     */
    public function deleteUnit(Unit $unit): void
    {
        if ($unit->products()->exists()) {
            throw new ConflictHttpException("Cannot delete unit '{$unit->name}' as it has associated products.");
        }

        if ($unit->subUnits()->exists()) {
            throw new ConflictHttpException("Cannot delete unit '{$unit->name}' as it has associated sub-units.");
        }

        DB::transaction(fn() => $unit->delete());
    }

    /**
     * Bulk delete units.
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
     * Update status for multiple units.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Unit::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import units from file.
     */
    public function importUnits(UploadedFile $file): void
    {
        ExcelFacade::import(new UnitsImport, $file);
    }

    /**
     * Download unit import template.
     */
    public function download(): string
    {
        $fileName = "units-sample.csv";
        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException("Template units not found.");
        }

        return $path;
    }

    /**
     * Export units to file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'units_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
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
