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
 */
class TaxService extends BaseService
{
    use MailInfo;
    /**
     * Get paginated list of taxes with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Tax>
     */
    public function getTaxes(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Tax::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%');
                })
            )
            ->orderBy('name')
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
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeTaxData($data);
            return Tax::create($data);
        });
    }

    /**
     * Normalize tax data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeTaxData(array $data): array
    {
        // is_active is stored as boolean (true/false)
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
     * Update an existing tax.
     *
     * @param Tax $tax Tax instance to update
     * @param array<string, mixed> $data Validated tax data
     * @return Tax
     */
    public function updateTax(Tax $tax, array $data): Tax
    {
        return $this->transaction(function () use ($tax, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeTaxData($data);
            $tax->update($data);
            return $tax->fresh();
        });
    }

    /**
     * Bulk delete multiple taxes.
     *
     * @param array<int> $ids Array of tax IDs to delete
     * @return int Number of taxes deleted
     */
    public function bulkDeleteTaxes(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $tax = Tax::findOrFail($id);
                $this->deleteTax($tax);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete tax {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single tax.
     *
     * @param Tax $tax Tax instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteTax(Tax $tax): bool
    {
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
        $this->transaction(function () use ($file) {
            Excel::import(new TaxesImport(), $file);
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
        return $this->transaction(function () use ($ids) {
            return Tax::whereIn('id', $ids)
                ->update(['is_active' => true]);
        });
    }

    /**
     * Bulk deactivate multiple taxes.
     *
     * @param array<int> $ids Array of tax IDs to deactivate
     * @return int Number of taxes deactivated
     */
    public function bulkDeactivateTaxes(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Tax::whereIn('id', $ids)
                ->update(['is_active' => false]);
        });
    }

    /**
     * Export taxes to Excel or PDF.
     *
     * @param array<int> $ids Array of tax IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @return string File path or download response
     */
    public function exportTaxes(array $ids = [], string $format = 'excel', ?User $user = null, array $columns = [], string $method = 'download'): string
    {
        $fileName = 'taxes-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $filePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new TaxesExport($ids, $columns), $filePath, 'public');
        } else {
            // For PDF, export data first then create PDF view
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

        // If user is provided, send email
        if ($user && $method === 'email') {
            // Check mail settings before attempting to send email
            $mailSetting = MailSetting::latest()->first();
            if (!$mailSetting) {
                throw new HttpResponseException(
                    response()->json([
                        'message' => 'Mail settings are not configured. Please contact the administrator.',
                    ], Response::HTTP_BAD_REQUEST)
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
                // Log error and return error response instead of aborting
                $this->logError("Failed to send export email: " . $e->getMessage());
                throw new HttpResponseException(
                    response()->json([
                        'message' => 'Failed to send export email: ' . $e->getMessage(),
                    ], Response::HTTP_INTERNAL_SERVER_ERROR)
                );
            }
        }

        return $filePath;
    }
}

