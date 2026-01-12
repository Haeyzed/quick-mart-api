<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\UnitsExport;
use App\Imports\UnitsImport;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Unit;
use App\Models\User;
use App\Traits\MailInfo;
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
 *
 * Key Features:
 * - Encapsulates all unit-related database queries
 * - Provides bulk operations for efficiency
 * - Manages data normalization and validation
 */
class UnitService extends BaseService
{
    use MailInfo;

    private const BULK_ACTIVATE = ['is_active' => true];
    private const BULK_DEACTIVATE = ['is_active' => false];

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
                    $search = $filters['search'];
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
            )
            ->latest()
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
            $data = $this->normalizeUnitData($data);
            return Unit::create($data);
        });
    }

    /**
     * Normalize unit data to match database schema requirements.
     *
     * Handles boolean conversions and default values:
     * - is_active: boolean, defaults to true on create
     * - Sets default operator and operation_value if base_unit is not set
     *
     * @param array<string, mixed> $data
     * @param bool $isUpdate Whether this is an update operation
     * @return array<string, mixed>
     */
    private function normalizeUnitData(array $data, bool $isUpdate = false): array
    {
        // Set default operator and operation_value if base_unit is not set
        // This matches the salespro logic: if no base_unit, set defaults
        if (empty($data['base_unit'])) {
            $data['operator'] = '*';
            $data['operation_value'] = 1;
            // Clear base_unit to ensure it's null
            $data['base_unit'] = null;
        }

        // is_active defaults to true on create
        if (!isset($data['is_active']) && !$isUpdate) {
            $data['is_active'] = true;
        } elseif (isset($data['is_active'])) {
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
            $data = $this->normalizeUnitData($data, isUpdate: true);
            $unit->update($data);
            return $unit->fresh();
        });
    }

    /**
     * Refactored from individual loop to batch processing with chunking
     * Bulk delete multiple units using efficient batch operations.
     *
     * @param array<int> $ids Array of unit IDs to delete
     * @return int Number of units successfully deleted
     */
    public function bulkDeleteUnits(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            $deletedCount = 0;

            Unit::whereIn('id', $ids)
                ->chunk(100, function ($units) use (&$deletedCount) {
                    foreach ($units as $unit) {
                        try {
                            if (!$unit->products()->exists() && !$unit->subUnits()->exists()) {
                                $unit->delete();
                                $deletedCount++;
                            }
                        } catch (Exception $e) {
                            $this->logError("Failed to delete unit {$unit->id}: " . $e->getMessage());
                        }
                    }
                });

            return $deletedCount;
        });
    }

    /**
     * Delete a single unit with validation.
     *
     * Prevents deletion if unit has associated products or sub-units.
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
     * Created private helper method to eliminate duplication across all bulk update methods
     * Bulk update multiple units with specified data.
     *
     * @param array<int> $ids Array of unit IDs to update
     * @param array<string, mixed> $updateData Data to update
     * @return int Number of units updated
     */
    private function bulkUpdateUnits(array $ids, array $updateData): int
    {
        return $this->transaction(function () use ($ids, $updateData) {
            return Unit::whereIn('id', $ids)->update($updateData);
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
        return $this->bulkUpdateUnits($ids, self::BULK_ACTIVATE);
    }

    /**
     * Bulk deactivate multiple units.
     *
     * @param array<int> $ids Array of unit IDs to deactivate
     * @return int Number of units deactivated
     */
    public function bulkDeactivateUnits(array $ids): int
    {
        return $this->bulkUpdateUnits($ids, self::BULK_DEACTIVATE);
    }

    /**
     * Export units to Excel or PDF.
     *
     * @param array<int> $ids Array of unit IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @param string $method Export method: 'download' or 'email'
     * @return string File path
     */
    public function exportUnits(
        array $ids = [],
        string $format = 'excel',
        ?User $user = null,
        array $columns = [],
        string $method = 'download'
    ): string {
        $fileName = 'units-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $filePath = 'exports/' . $fileName;

        $this->generateExportFile($format, $filePath, $ids, $columns);

        if ($user && $method === 'email') {
            $this->sendExportEmail($user, $filePath, $fileName);
        }

        return $filePath;
    }

    /**
     * Extracted export file generation logic for cleaner separation of concerns
     * Generate export file in specified format.
     *
     * @param string $format
     * @param string $filePath
     * @param array<int> $ids
     * @param array<string> $columns
     * @return void
     */
    private function generateExportFile(string $format, string $filePath, array $ids, array $columns): void
    {
        if ($format === 'excel') {
            Excel::store(new UnitsExport($ids, $columns), $filePath, 'public');
        } else {
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
    }

    /**
     * Extracted email sending logic for better error handling and maintainability
     * Send export file via email.
     *
     * @param User $user
     * @param string $filePath
     * @param string $fileName
     * @return void
     * @throws HttpResponseException
     */
    private function sendExportEmail(User $user, string $filePath, string $fileName): void
    {
        $mailSetting = MailSetting::latest()->first();
        if (!$mailSetting) {
            throw new HttpResponseException(
                response()->json(
                    ['message' => 'Mail settings are not configured. Please contact the administrator.'],
                    Response::HTTP_BAD_REQUEST
                )
            );
        }

        $generalSetting = GeneralSetting::latest()->first();

        try {
            $this->setMailInfo($mailSetting);
            Mail::to($user->email)->send(new ExportMail(
                $user,
                $filePath,
                $fileName,
                'Units',
                $generalSetting
            ));
        } catch (Exception $e) {
            $this->logError("Failed to send export email: " . $e->getMessage());
            throw new HttpResponseException(
                response()->json(
                    ['message' => 'Failed to send export email: ' . $e->getMessage()],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                )
            );
        }
    }
}

