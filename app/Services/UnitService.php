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
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Service class for Unit entity lifecycle operations.
 *
 * Centralizes business logic for Unit CRUD, bulk actions, imports/exports.
 * Delegates permission checks to CheckPermissionsTrait.
 */
class UnitService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * Create a new UnitService instance.
     */
    public function __construct()
    {
    }

    /**
     * Retrieve a single unit by instance.
     *
     * Requires units-index permission. Use for show/display operations.
     *
     * @param Unit $unit The unit instance to retrieve.
     * @return Unit The refreshed unit instance.
     */
    public function getUnit(Unit $unit): Unit
    {
        $this->requirePermission('units-index');

        return $unit->fresh(['baseUnitRelation']);
    }

    /**
     * Retrieve units with optional filters and pagination.
     *
     * Supports filtering by status (active/inactive) and search term.
     * Requires units-index permission.
     *
     * @param array<string, mixed> $filters Associative array with optional keys: 'status', 'search'.
     * @param int $perPage Number of items per page.
     * @return LengthAwarePaginator<Unit> Paginated unit collection.
     */
    public function getUnits(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('units-index');

        return Unit::query()
            ->with('baseUnitRelation')
            ->when(isset($filters['status']), fn($q) => $q->where('is_active', $filters['status'] === 'active')
            )
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn($subQ) => $subQ
                    ->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term)
                );
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all active base units (units where base_unit is null).
     * Used for populating base unit dropdowns.
     *
     * @return Collection<int, Unit>
     */
    public function getBaseUnits(): Collection
    {
        $this->requirePermission('units-index');

        return Unit::where('is_active', true)
            ->whereNull('base_unit')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new unit.
     *
     * Requires units-create permission.
     *
     * @param array<string, mixed> $data Validated unit data.
     * @return Unit The created unit instance.
     */
    public function createUnit(array $data): Unit
    {
        $this->requirePermission('units-create');

        return DB::transaction(function () use ($data) {
            $data = $this->normalizeUnitData($data);

            return Unit::create($data);
        });
    }

    /**
     * Normalize unit data to match database schema requirements.
     *
     * Handles boolean conversions and default values: is_active, operator, operation_value.
     *
     * @param array<string, mixed> $data Input data.
     * @param bool $isUpdate Whether this is an update operation.
     * @return array<string, mixed> Normalized data.
     */
    private function normalizeUnitData(array $data, bool $isUpdate = false): array
    {
        if (empty($data['base_unit'])) {
            $data['operator'] = '*';
            $data['operation_value'] = 1;
            $data['base_unit'] = null;
        }

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
     * Requires units-update permission.
     *
     * @param Unit $unit The unit instance to update.
     * @param array<string, mixed> $data Validated unit data.
     * @return Unit The updated unit instance (refreshed).
     */
    public function updateUnit(Unit $unit, array $data): Unit
    {
        $this->requirePermission('units-update');

        return DB::transaction(function () use ($unit, $data) {
            $data = $this->normalizeUnitData($data, isUpdate: true);
            $unit->update($data);

            return $unit->fresh();
        });
    }

    /**
     * Soft-delete a unit.
     *
     * Fails if the unit has associated products or sub-units.
     * Requires units-delete permission.
     *
     * @param Unit $unit The unit instance to delete.
     *
     * @throws ConflictHttpException When unit has associated products or sub-units (409 Conflict).
     */
    public function deleteUnit(Unit $unit): void
    {
        $this->requirePermission('units-delete');

        if ($unit->products()->exists()) {
            throw new ConflictHttpException(
                "Cannot delete unit '{$unit->name}' because it has associated products."
            );
        }

        if ($unit->subUnits()->exists()) {
            throw new ConflictHttpException(
                "Cannot delete unit '{$unit->name}' because it has associated sub-units."
            );
        }

        $unit->delete();
    }

    /**
     * Bulk soft-delete units that have no associated products or sub-units.
     *
     * Skips units with products or sub-units. Returns the count of successfully deleted units.
     * Requires units-delete permission.
     *
     * @param array<int> $ids Unit IDs to delete.
     * @return int Number of units successfully deleted.
     */
    public function bulkDeleteUnits(array $ids): int
    {
        $this->requirePermission('units-delete');

        return DB::transaction(function () use ($ids) {
            $units = Unit::whereIn('id', $ids)
                ->withCount(['products', 'subUnits'])
                ->get();

            $deletedCount = 0;

            foreach ($units as $unit) {
                if ($unit->products_count > 0 || $unit->sub_units_count > 0) {
                    continue;
                }

                $unit->delete();
                $deletedCount++;
            }

            return $deletedCount;
        });
    }

    /**
     * Bulk activate units by ID.
     *
     * Sets is_active to true for all matching units. Requires units-update permission.
     *
     * @param array<int> $ids Unit IDs to activate.
     * @return int Number of units updated.
     */
    public function bulkActivateUnits(array $ids): int
    {
        $this->requirePermission('units-update');

        return Unit::whereIn('id', $ids)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate units by ID.
     *
     * Sets is_active to false for all matching units. Requires units-update permission.
     *
     * @param array<int> $ids Unit IDs to deactivate.
     * @return int Number of units updated.
     */
    public function bulkDeactivateUnits(array $ids): int
    {
        $this->requirePermission('units-update');

        return Unit::whereIn('id', $ids)->update(['is_active' => false]);
    }

    /**
     * Import units from an Excel or CSV file.
     *
     * Requires units-import permission.
     *
     * @param UploadedFile $file The uploaded import file.
     */
    public function importUnits(UploadedFile $file): void
    {
        $this->requirePermission('units-import');
        Excel::import(new UnitsImport, $file);
    }

    /**
     * Export units to Excel or PDF file.
     *
     * Supports download or email delivery. Requires units-export permission.
     *
     * @param array<int> $ids Unit IDs to export. Empty array exports all.
     * @param string $format Export format: 'excel' or 'pdf'.
     * @param User|null $user Recipient when method is 'email'. Required for email delivery.
     * @param array<string> $columns Column keys to include in export.
     * @param string $method Delivery method: 'download' or 'email'.
     * @return string Relative storage path of the generated file.
     *
     * @throws RuntimeException When mail settings are not configured and method is 'email'.
     */
    public function exportUnits(
        array  $ids,
        string $format,
        ?User  $user,
        array  $columns,
        string $method
    ): string
    {
        $this->requirePermission('units-export');

        $fileName = 'units_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new UnitsExport($ids, $columns), $relativePath, 'public');
        } else {
            $units = Unit::query()
                ->with('baseUnitRelation:id,code,name')
                ->when(!empty($ids), fn($q) => $q->whereIn('id', $ids))
                ->orderBy('code')
                ->get();

            $pdf = PDF::loadView('exports.units-pdf', compact('units', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName);
        }

        return $relativePath;
    }

    /**
     * Send export completion email to the user.
     *
     * @param User $user Recipient of the export email.
     * @param string $path Relative storage path of the export file.
     * @param string $fileName Display filename for the attachment.
     *
     * @throws RuntimeException When mail settings are not configured.
     */
    private function sendExportEmail(User $user, string $path, string $fileName): void
    {
        $mailSetting = MailSetting::default()->firstOr(
            fn() => throw new RuntimeException('Mail settings are not configured.')
        );
        $generalSetting = GeneralSetting::latest()->first();

        $this->setMailInfo($mailSetting);

        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, 'Units List', $generalSetting)
        );
    }
}
