<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\User;
use App\Models\Warehouse;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

/**
 * Service class for Warehouse entity lifecycle operations.
 *
 * Centralizes business logic for Warehouse CRUD, bulk actions, imports, and exports.
 * Delegates permission checks to CheckPermissionsTrait.
 * Warehouse "delete" means deactivate (set is_active = false) per quick-mart-old logic.
 */
class WarehouseService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * Retrieve a single warehouse by instance.
     *
     * Requires warehouses-index permission. Use for show/display operations.
     *
     * @param Warehouse $warehouse The warehouse instance to retrieve.
     * @return Warehouse The refreshed warehouse instance.
     */
    public function getWarehouse(Warehouse $warehouse): Warehouse
    {
        $this->requirePermission('warehouses-index');

        return $warehouse->fresh();
    }

    /**
     * Retrieve warehouses with optional filters and pagination.
     *
     * Supports filtering by status (active/inactive) and search term.
     * Requires warehouses-index permission.
     *
     * @param array<string, mixed> $filters Associative array with optional keys: 'status', 'search'.
     * @param int $perPage Number of items per page.
     * @return LengthAwarePaginator<Warehouse> Paginated warehouse collection.
     */
    public function getWarehouses(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('warehouses-index');

        return Warehouse::query()
            ->withCount(['productWarehouses as number_of_products' => fn($q) => $q->where('qty', '>', 0)])
            ->withSum(['productWarehouses as stock_quantity' => fn($q) => $q->where('qty', '>', 0)], 'qty')
            ->when(isset($filters['status']), fn($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when(!empty($filters['search'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn($subQ) => $subQ
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                );
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Create a new warehouse.
     *
     * Creates ProductWarehouse entries for all existing products (qty=0) per quick-mart-old logic.
     * Requires warehouses-create permission.
     *
     * @param array<string, mixed> $data Validated warehouse attributes.
     * @return Warehouse The created warehouse instance.
     */
    public function createWarehouse(array $data): Warehouse
    {
        $this->requirePermission('warehouses-create');

        return DB::transaction(function () use ($data) {
            $data = $this->normalizeWarehouseData($data);
            $warehouse = Warehouse::create($data);

            // Create ProductWarehouse entries for all existing products (per quick-mart-old)
            $productIds = Product::pluck('id');
            foreach ($productIds as $productId) {
                ProductWarehouse::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouse->id,
                    'qty' => 0,
                ]);
            }

            return $warehouse;
        });
    }

    /**
     * Normalize warehouse data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeWarehouseData(array $data): array
    {
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
     * Update an existing warehouse.
     *
     * Requires warehouses-update permission.
     *
     * @param Warehouse $warehouse The warehouse instance to update.
     * @param array<string, mixed> $data Validated warehouse attributes.
     * @return Warehouse The updated warehouse instance (refreshed).
     */
    public function updateWarehouse(Warehouse $warehouse, array $data): Warehouse
    {
        $this->requirePermission('warehouses-update');

        return DB::transaction(function () use ($warehouse, $data) {
            $data = $this->normalizeWarehouseData($data);
            $warehouse->update($data);

            return $warehouse->fresh();
        });
    }

    /**
     * Deactivate a warehouse (set is_active = false).
     *
     * Per quick-mart-old: warehouse delete means deactivate, not soft delete.
     * Requires warehouses-delete permission.
     *
     * @param Warehouse $warehouse The warehouse instance to delete.
     */
    public function deleteWarehouse(Warehouse $warehouse): void
    {
        $this->requirePermission('warehouses-delete');

        DB::transaction(function () use ($warehouse) {
            $warehouse->deactivate();
        });
    }

    /**
     * Bulk deactivate warehouses by ID.
     *
     * Skips non-existent IDs. Returns the count of successfully deactivated warehouses.
     * Requires warehouses-delete permission.
     *
     * @param array<int> $ids Warehouse IDs to delete (deactivate).
     * @return int Number of warehouses successfully deactivated.
     */
    public function bulkDeleteWarehouses(array $ids): int
    {
        $this->requirePermission('warehouses-delete');

        return DB::transaction(function () use ($ids) {
            $warehouses = Warehouse::whereIn('id', $ids)->get();
            $deletedCount = 0;

            foreach ($warehouses as $warehouse) {
                $warehouse->deactivate();
                $deletedCount++;
            }

            return $deletedCount;
        });
    }

    /**
     * Bulk activate warehouses by ID.
     *
     * Sets is_active to true for all matching warehouses. Requires warehouses-update permission.
     *
     * @param array<int> $ids Warehouse IDs to activate.
     * @return int Number of warehouses updated.
     */
    public function bulkActivateWarehouses(array $ids): int
    {
        $this->requirePermission('warehouses-update');

        return Warehouse::whereIn('id', $ids)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate warehouses by ID.
     *
     * Sets is_active to false for all matching warehouses. Requires warehouses-update permission.
     *
     * @param array<int> $ids Warehouse IDs to deactivate.
     * @return int Number of warehouses updated.
     */
    public function bulkDeactivateWarehouses(array $ids): int
    {
        $this->requirePermission('warehouses-update');

        return Warehouse::whereIn('id', $ids)->update(['is_active' => false]);
    }

    /**
     * Import warehouses from an Excel or CSV file.
     *
     * Uses WarehousesImport for upsert logic (name, phone, email, address). Requires warehouses-import permission.
     *
     * @param UploadedFile $file The uploaded import file.
     */
    public function importWarehouses(UploadedFile $file): void
    {
        $this->requirePermission('warehouses-import');

        Excel::import(new WarehousesImport, $file);
    }

    /**
     * Export warehouses to Excel or PDF file.
     *
     * Supports download or email delivery. Requires warehouses-export permission.
     *
     * @param array<int> $ids Warehouse IDs to export. Empty array exports all.
     * @param string $format Export format: 'excel' or 'pdf'.
     * @param User|null $user Recipient when method is 'email'. Required for email delivery.
     * @param array<string> $columns Column keys to include in export.
     * @param string $method Delivery method: 'download' or 'email'.
     * @return string Relative storage path of the generated file.
     *
     * @throws RuntimeException When mail settings are not configured and method is 'email'.
     */
    public function exportWarehouses(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('warehouses-export');

        $fileName = 'warehouses_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new WarehousesExport($ids, $columns), $relativePath, 'public');
        } else {
            $warehouses = Warehouse::query()
                ->withCount(['productWarehouses as number_of_products' => fn($q) => $q->where('qty', '>', 0)])
                ->withSum(['productWarehouses as stock_quantity' => fn($q) => $q->where('qty', '>', 0)], 'qty')
                ->when(!empty($ids), fn($q) => $q->whereIn('id', $ids))
                ->orderBy('name')
                ->get();

            $pdf = PDF::loadView('exports.warehouses-pdf', compact('warehouses', 'columns'));
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
            new ExportMail($user, $path, $fileName, 'Warehouses List', $generalSetting)
        );
    }

    /**
     * Get all active warehouses.
     *
     * Role-based: users with role_id > 2 only see their assigned warehouse (per quick-mart-old warehouseAll).
     * Requires warehouses-index permission.
     *
     * @param User|null $user Optional user instance (defaults to authenticated user).
     * @return Collection<int, Warehouse>
     */
    public function getAllActive(?User $user = null): Collection
    {
        $this->requirePermission('warehouses-index');

        $user = $user ?? Auth::user();

        $query = Warehouse::query()
            ->withCount(['productWarehouses as number_of_products' => fn($q) => $q->where('qty', '>', 0)])
            ->withSum(['productWarehouses as stock_quantity' => fn($q) => $q->where('qty', '>', 0)], 'qty');

        if ($user && $user->role_id > 2 && $user->warehouse_id) {
            return $query->where('is_active', true)
                ->where('id', $user->warehouse_id)
                ->get();
        }

        return $query->where('is_active', true)->get();
    }
}
