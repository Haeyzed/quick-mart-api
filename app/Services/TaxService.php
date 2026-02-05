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
 * TaxService
 *
 * Handles all business logic for tax operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 *
 * Key Features:
 * - Encapsulates all tax-related database queries
 * - Provides bulk operations for efficiency
 * - Manages data normalization and validation
 * - Enforces permission checks for all operations
 */
class TaxService extends BaseService
{
    use CheckPermissionsTrait;
    use MailInfo;

    private const BULK_ACTIVATE = ['is_active' => true];
    private const BULK_DEACTIVATE = ['is_active' => false];

    /**
     * Get paginated list of taxes with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Tax>
     */
    public function getTaxes(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        // Check permission: user needs 'tax' permission to view taxes
        $this->requirePermission('tax');

        return Tax::query()
            ->when(
                isset($filters['status']),
                fn($query) => $query->where('is_active', $filters['status'] === 'active')
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $search = $filters['search'];
                    $q->where('name', 'like', "%{$search}%");
                })
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single tax by ID.
     *
     * @param int $id Tax ID
     * @return Tax
     */
    public function getTax(int $id): Tax
    {
        // Check permission: user needs 'tax' permission to view taxes
        $this->requirePermission('tax');

        return Tax::findOrFail($id);
    }

    /**
     * Create a new tax.
     *
     * @param array<string, mixed> $data Validated tax data
     * @return Tax
     */
    public function createTax(array $data): Tax
    {
        // Check permission: user needs 'tax' permission to create taxes
        $this->requirePermission('tax');

        return $this->transaction(function () use ($data) {
            $data = $this->normalizeTaxData($data);
            return Tax::create($data);
        });
    }

    /**
     * Normalize tax data to match database schema requirements.
     *
     * Handles boolean conversions and default values:
     * - is_active: boolean, defaults to true on create
     *
     * @param array<string, mixed> $data
     * @param bool $isUpdate Whether this is an update operation
     * @return array<string, mixed>
     */
    private function normalizeTaxData(array $data, bool $isUpdate = false): array
    {
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
     * Update an existing tax.
     *
     * @param Tax $tax Tax instance to update
     * @param array<string, mixed> $data Validated tax data
     * @return Tax
     */
    public function updateTax(Tax $tax, array $data): Tax
    {
        // Check permission: user needs 'tax' permission to update taxes
        $this->requirePermission('tax');

        return $this->transaction(function () use ($tax, $data) {
            $data = $this->normalizeTaxData($data, isUpdate: true);
            $tax->update($data);
            return $tax->fresh();
        });
    }

    /**
     * Refactored from individual loop to batch processing with chunking
     * Bulk delete multiple taxes using efficient batch operations.
     *
     * @param array<int> $ids Array of tax IDs to delete
     * @return int Number of taxes successfully deleted
     */
    public function bulkDeleteTaxes(array $ids): int
    {
        // Check permission: user needs 'tax' permission to delete taxes
        $this->requirePermission('tax');

        return $this->transaction(function () use ($ids) {
            $deletedCount = 0;

            Tax::whereIn('id', $ids)
                ->chunk(100, function ($taxes) use (&$deletedCount) {
                    foreach ($taxes as $tax) {
                        try {
                            if (!$tax->products()->exists()) {
                                $tax->delete();
                                $deletedCount++;
                            }
                        } catch (Exception $e) {
                            $this->logError("Failed to delete tax {$tax->id}: " . $e->getMessage());
                        }
                    }
                });

            return $deletedCount;
        });
    }

    /**
     * Delete a single tax with validation.
     *
     * Prevents deletion if tax has associated products.
     *
     * @param Tax $tax Tax instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteTax(Tax $tax): bool
    {
        // Check permission: user needs 'tax' permission to delete taxes
        $this->requirePermission('tax');

        return $this->transaction(function () use ($tax) {
            if ($tax->products()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete tax: tax has associated products');
            }

            return $tax->delete();
        });
    }

    /**
     * Import taxes from a file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importTaxes(UploadedFile $file): void
    {
        // Check permission: user needs 'tax' permission to import taxes
        $this->requirePermission('tax');

        $this->transaction(function () use ($file) {
            Excel::import(new TaxesImport(), $file);
        });
    }

    /**
     * Created private helper method to eliminate duplication across all bulk update methods
     * Bulk update multiple taxes with specified data.
     *
     * @param array<int> $ids Array of tax IDs to update
     * @param array<string, mixed> $updateData Data to update
     * @return int Number of taxes updated
     */
    private function bulkUpdateTaxes(array $ids, array $updateData): int
    {
        return $this->transaction(function () use ($ids, $updateData) {
            return Tax::whereIn('id', $ids)->update($updateData);
        });
    }

    /**
     * Bulk activate multiple taxes.
     *
     * @param array<int> $ids Array of tax IDs to activate
     * @return int Number of taxes activated
     */
    public function bulkActivateTaxes(array $ids): int
    {
        // Check permission: user needs 'tax' permission to update taxes
        $this->requirePermission('tax');

        return $this->bulkUpdateTaxes($ids, self::BULK_ACTIVATE);
    }

    /**
     * Bulk deactivate multiple taxes.
     *
     * @param array<int> $ids Array of tax IDs to deactivate
     * @return int Number of taxes deactivated
     */
    public function bulkDeactivateTaxes(array $ids): int
    {
        // Check permission: user needs 'tax' permission to update taxes
        $this->requirePermission('tax');

        return $this->bulkUpdateTaxes($ids, self::BULK_DEACTIVATE);
    }

    /**
     * Export taxes to Excel or PDF.
     *
     * @param array<int> $ids Array of tax IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @param string $method Export method: 'download' or 'email'
     * @return string File path
     */
    public function exportTaxes(
        array $ids = [],
        string $format = 'excel',
        ?User $user = null,
        array $columns = [],
        string $method = 'download'
    ): string {
        // Check permission: user needs 'tax' permission to export taxes
        $this->requirePermission('tax');

        $fileName = 'taxes-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
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
            Excel::store(new TaxesExport($ids, $columns), $filePath, 'public');
        } else {
            $taxes = Tax::query()
                ->when(!empty($ids), fn($query) => $query->whereIn('id', $ids))
                ->orderBy('name')
                ->get();

            $pdf = PDF::loadView('exports.taxes-pdf', [
                'taxes' => $taxes,
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
                'Taxes',
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

