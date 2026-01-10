<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\UnitsExport;
use App\Imports\UnitsImport;
use App\Mail\ExportMail;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/**
 * UnitService
 *
 * Handles all business logic for unit operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class UnitService extends BaseService
{
    /**
     * Get paginated list of units with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Unit>
     */
    public function getUnits(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Unit::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $q->where('code', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('name', 'like', '%' . $filters['search'] . '%');
                })
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single unit by ID.
     *
     * @param int $id Unit ID
     * @return Unit
     */
    public function getUnit(int $id): Unit
    {
        return Unit::findOrFail($id);
    }

    /**
     * Get all active base units (units where base_unit is null).
     * Used for populating base unit dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Unit>
     */
    public function getBaseUnits(): \Illuminate\Database\Eloquent\Collection
    {
        return Unit::where('is_active', true)
            ->whereNull('base_unit')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new unit.
     *
     * @param array<string, mixed> $data Validated unit data
     * @return Unit
     */
    public function createUnit(array $data): Unit
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeUnitData($data);
            return Unit::create($data);
        });
    }

    /**
     * Normalize unit data to match database schema requirements.
     *
     * Sets default operator and operation_value if base_unit is not set.
     * Normalizes boolean fields.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeUnitData(array $data): array
    {
        // Set default operator and operation_value if base_unit is not set
        // This matches the salespro logic: if no base_unit, set defaults
        if (empty($data['base_unit'])) {
            $data['operator'] = '*';
            $data['operation_value'] = 1;
            // Clear base_unit to ensure it's null
            $data['base_unit'] = null;
        }

        // Normalize boolean fields to match database schema
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $data;
    }

    /**
     * Update an existing unit.
     *
     * @param Unit $unit Unit instance to update
     * @param array<string, mixed> $data Validated unit data
     * @return Unit
     */
    public function updateUnit(Unit $unit, array $data): Unit
    {
        return $this->transaction(function () use ($unit, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeUnitData($data);
            $unit->update($data);
            return $unit->fresh();
        });
    }

    /**
     * Bulk delete multiple units.
     *
     * @param array<int> $ids Array of unit IDs to delete
     * @return int Number of units deleted
     */
    public function bulkDeleteUnits(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $unit = Unit::findOrFail($id);
                $this->deleteUnit($unit);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete unit {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single unit.
     *
     * @param Unit $unit Unit instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteUnit(Unit $unit): bool
    {
        return $this->transaction(function () use ($unit) {
            if ($unit->products()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete unit: unit has associated products');
            }

            if ($unit->subUnits()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete unit: unit has associated sub-units');
            }

            return $unit->delete();
        });
    }

    /**
     * Import units from a file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importUnits(UploadedFile $file): void
    {
        $this->transaction(function () use ($file) {
            Excel::import(new UnitsImport(), $file);
        });
    }

    /**
     * Bulk activate multiple units.
     *
     * @param array<int> $ids Array of unit IDs to activate
     * @return int Number of units activated
     */
    public function bulkActivateUnits(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Unit::whereIn('id', $ids)
                ->update(['is_active' => true]);
        });
    }

    /**
     * Bulk deactivate multiple units.
     *
     * @param array<int> $ids Array of unit IDs to deactivate
     * @return int Number of units deactivated
     */
    public function bulkDeactivateUnits(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Unit::whereIn('id', $ids)
                ->update(['is_active' => false]);
        });
    }

    /**
     * Export units to Excel or PDF.
     *
     * @param array<int> $ids Array of unit IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @return string File path or download response
     */
    public function exportUnits(array $ids = [], string $format = 'excel', ?User $user = null, array $columns = []): string
    {
        $fileName = 'units-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $filePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new UnitsExport($ids, $columns), $filePath, 'public');
        } else {
            // For PDF, export data first then create PDF view
            $units = Unit::with('baseUnitRelation:id,code,name')
                ->when(!empty($ids), fn($query) => $query->whereIn('id', $ids))
                ->orderBy('code')
                ->get();

            $pdf = PDF::loadView('exports.units-pdf', [
                'units' => $units,
                'columns' => $columns,
            ]);
            Storage::disk('public')->put($filePath, $pdf->output());
        }

        // If user is provided, send email
        if ($user) {
            Mail::to($user->email)->send(new ExportMail(
                $user,
                $filePath,
                $fileName,
                'Units'
            ));
        }

        return $filePath;
    }
}

