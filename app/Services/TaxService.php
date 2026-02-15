<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\TaxesExport;
use App\Imports\TaxesImport;
use App\Models\Tax;
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
 * Class TaxService
 * Handles business logic for Taxes.
 */
class TaxService
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
     * Get paginated taxes based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedTaxes(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Tax::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of unit options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Tax::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn(Tax $tax) => [
                'value' => $tax->id,
                'label' => $tax->name,
            ]);
    }

    /**
     * Create a new tax.
     *
     * @param array<string, mixed> $data
     */
    public function createTax(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            return Tax::query()->create($data);
        });
    }

    /**
     * Update an existing tax.
     *
     * @param array<string, mixed> $data
     */
    public function updateTax(Tax $tax, array $data): Tax
    {
        return DB::transaction(function () use ($tax, $data) {
            $tax->update($data);
            return $tax->fresh();
        });
    }

    /**
     * Delete a tax.
     *
     * @throws ConflictHttpException
     */
    public function deleteTax(Tax $tax): void
    {
        if ($tax->products()->exists()) {
            throw new ConflictHttpException("Cannot delete tax '{$tax->name}' as it has associated products.");
        }

        DB::transaction(function () use ($tax) {
            $tax->delete();
        });
    }

    /**
     * Bulk delete taxes.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteTaxes(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $taxes = Tax::query()->whereIn('id', $ids)->withCount('products')->get();
            $count = 0;

            foreach ($taxes as $tax) {
                if ($tax->products_count > 0) {
                    continue;
                }
                $tax->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update status for multiple taxes.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Tax::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import taxes from file.
     */
    public function importTaxes(UploadedFile $file): void
    {
        ExcelFacade::import(new TaxesImport, $file);
    }

    /**
     * Download a taxes CSV template.
     */
    public function download(): string
    {
        $fileName = "taxes-sample.csv";

        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException("Template taxes not found.");
        }

        return $path;
    }

    /**
     * Export taxes to file.
     *
     * @param array<int> $ids
     * @param string $format 'excel' or 'pdf'
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters
     * @return string Relative file path.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'taxes_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new TaxesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
