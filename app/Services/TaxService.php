<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\TaxesExport;
use App\Imports\TaxesImport;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Tax;
use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Service class for Tax entity lifecycle operations.
 *
 * Centralizes business logic for Tax CRUD, bulk actions, imports/exports.
 * Delegates permission checks to CheckPermissionsTrait.
 */
class TaxService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * Create a new TaxService instance.
     */
    public function __construct()
    {
    }

    /**
     * Retrieve a single tax by instance.
     *
     * Requires taxes-index permission. Use for show/display operations.
     *
     * @param Tax $tax The tax instance to retrieve.
     * @return Tax The refreshed tax instance.
     */
    public function getTax(Tax $tax): Tax
    {
        $this->requirePermission('taxes-index');

        return $tax->fresh();
    }

    /**
     * Retrieve taxes with optional filters and pagination.
     *
     * Supports filtering by status (active/inactive) and search term.
     * Requires taxes-index permission.
     *
     * @param array<string, mixed> $filters Associative array with optional keys: 'status', 'search'.
     * @param int $perPage Number of items per page.
     * @return LengthAwarePaginator<Tax> Paginated tax collection.
     */
    public function getTaxes(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('taxes-index');

        return Tax::query()
            ->when(isset($filters['status']), fn($q) => $q->where('is_active', $filters['status'] === 'active')
            )
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where('name', 'like', $term);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new tax.
     *
     * Requires taxes-create permission.
     *
     * @param array<string, mixed> $data Validated tax data.
     * @return Tax The created tax instance.
     */
    public function createTax(array $data): Tax
    {
        $this->requirePermission('taxes-create');

        return DB::transaction(function () use ($data) {
            $data = $this->normalizeTaxData($data);

            return Tax::create($data);
        });
    }

    /**
     * Normalize tax data to match database schema requirements.
     *
     * Handles boolean conversions and default values for is_active.
     *
     * @param array<string, mixed> $data Input data.
     * @param bool $isUpdate Whether this is an update operation.
     * @return array<string, mixed> Normalized data.
     */
    private function normalizeTaxData(array $data, bool $isUpdate = false): array
    {
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
     * Update an existing tax.
     *
     * Requires taxes-update permission.
     *
     * @param Tax $tax The tax instance to update.
     * @param array<string, mixed> $data Validated tax data.
     * @return Tax The updated tax instance (refreshed).
     */
    public function updateTax(Tax $tax, array $data): Tax
    {
        $this->requirePermission('taxes-update');

        return DB::transaction(function () use ($tax, $data) {
            $data = $this->normalizeTaxData($data, isUpdate: true);
            $tax->update($data);

            return $tax->fresh();
        });
    }

    /**
     * Soft-delete a tax.
     *
     * Fails if the tax has associated products.
     * Requires taxes-delete permission.
     *
     * @param Tax $tax The tax instance to delete.
     *
     * @throws ConflictHttpException When tax has associated products (409 Conflict).
     */
    public function deleteTax(Tax $tax): void
    {
        $this->requirePermission('taxes-delete');

        if ($tax->products()->exists()) {
            throw new ConflictHttpException(
                "Cannot delete tax '{$tax->name}' because it has associated products."
            );
        }

        $tax->delete();
    }

    /**
     * Bulk soft-delete taxes that have no associated products.
     *
     * Skips taxes with products. Returns the count of successfully deleted taxes.
     * Requires taxes-delete permission.
     *
     * @param array<int> $ids Tax IDs to delete.
     * @return int Number of taxes successfully deleted.
     */
    public function bulkDeleteTaxes(array $ids): int
    {
        $this->requirePermission('taxes-delete');

        return DB::transaction(function () use ($ids) {
            $taxes = Tax::whereIn('id', $ids)
                ->withCount('products')
                ->get();

            $deletedCount = 0;

            foreach ($taxes as $tax) {
                if ($tax->products_count > 0) {
                    continue;
                }

                $tax->delete();
                $deletedCount++;
            }

            return $deletedCount;
        });
    }

    /**
     * Bulk activate taxes by ID.
     *
     * Sets is_active to true for all matching taxes. Requires taxes-update permission.
     *
     * @param array<int> $ids Tax IDs to activate.
     * @return int Number of taxes updated.
     */
    public function bulkActivateTaxes(array $ids): int
    {
        $this->requirePermission('taxes-update');

        return Tax::whereIn('id', $ids)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate taxes by ID.
     *
     * Sets is_active to false for all matching taxes. Requires taxes-update permission.
     *
     * @param array<int> $ids Tax IDs to deactivate.
     * @return int Number of taxes updated.
     */
    public function bulkDeactivateTaxes(array $ids): int
    {
        $this->requirePermission('taxes-update');

        return Tax::whereIn('id', $ids)->update(['is_active' => false]);
    }

    /**
     * Import taxes from an Excel or CSV file.
     *
     * Requires taxes-import permission.
     *
     * @param UploadedFile $file The uploaded import file.
     */
    public function importTaxes(UploadedFile $file): void
    {
        $this->requirePermission('taxes-import');
        Excel::import(new TaxesImport, $file);
    }

    /**
     * Export taxes to Excel or PDF file.
     *
     * Supports download or email delivery. Requires taxes-export permission.
     *
     * @param array<int> $ids Tax IDs to export. Empty array exports all.
     * @param string $format Export format: 'excel' or 'pdf'.
     * @param User|null $user Recipient when method is 'email'. Required for email delivery.
     * @param array<string> $columns Column keys to include in export.
     * @param string $method Delivery method: 'download' or 'email'.
     * @return string Relative storage path of the generated file.
     *
     * @throws RuntimeException When mail settings are not configured and method is 'email'.
     */
    public function exportTaxes(
        array  $ids,
        string $format,
        ?User  $user,
        array  $columns,
        string $method
    ): string
    {
        $this->requirePermission('taxes-export');

        $fileName = 'taxes_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new TaxesExport($ids, $columns), $relativePath, 'public');
        } else {
            $taxes = Tax::query()
                ->when(!empty($ids), fn($q) => $q->whereIn('id', $ids))
                ->orderBy('name')
                ->get();

            $pdf = PDF::loadView('exports.taxes-pdf', compact('taxes', 'columns'));
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
            new ExportMail($user, $path, $fileName, 'Taxes List', $generalSetting)
        );
    }
}
