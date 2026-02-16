<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\BillersExport;
use App\Imports\BillersImport;
use App\Models\Biller;
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
 * Class BillerService
 * Handles business logic for Billers.
 */
class BillerService
{
    /**
     *
     */
    private const IMAGE_PATH = 'images/billers';
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
     * Get paginated billers based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedBillers(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Biller::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of biller options.
     * Returns value/label format for select/combobox components.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return Biller::active()
            ->select('id', 'name', 'company_name')
            ->orderBy('name')
            ->get()
            ->map(fn(Biller $biller) => [
                'value' => $biller->id,
                'label' => $biller->company_name ? "{$biller->name} ({$biller->company_name})" : $biller->name,
            ]);
    }

    /**
     * Create a new biller.
     *
     * @param array<string, mixed> $data
     */
    public function createBiller(array $data): Biller
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);
            return Biller::query()->create($data);
        });
    }

    /**
     * Handle Image Upload via UploadService.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function handleUploads(array $data, ?Biller $biller = null): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($biller?->image) {
                $this->uploadService->delete($biller->image);
            }
            $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    /**
     * Update an existing biller.
     *
     * @param array<string, mixed> $data
     */
    public function updateBiller(Biller $biller, array $data): Biller
    {
        return DB::transaction(function () use ($biller, $data) {
            $data = $this->handleUploads($data, $biller);
            $biller->update($data);
            return $biller->fresh();
        });
    }

    /**
     * Delete a biller.
     *
     * @throws ConflictHttpException
     */
    public function deleteBiller(Biller $biller): void
    {
        if ($biller->sales()->exists()) {
            throw new ConflictHttpException("Cannot delete biller '{$biller->name}' as it has associated sales.");
        }

        DB::transaction(function () use ($biller) {
            $this->cleanupFiles($biller);
            $biller->delete();
        });
    }

    /**
     * Remove associated files.
     */
    private function cleanupFiles(Biller $biller): void
    {
        if ($biller->image) {
            $this->uploadService->delete($biller->image);
        }
    }

    /**
     * Bulk delete billers.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteBillers(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $billers = Biller::query()->whereIn('id', $ids)->withCount('sales')->get();
            $count = 0;

            foreach ($billers as $biller) {
                if ($biller->sales_count > 0) {
                    continue;
                }

                $this->cleanupFiles($biller);
                $biller->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update status for multiple billers.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Biller::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import billers from file.
     */
    public function importBillers(UploadedFile $file): void
    {
        ExcelFacade::import(new BillersImport, $file);
    }

    /**
     * Download a billers CSV template.
     */
    public function download(): string
    {
        $fileName = "billers-sample.csv";

        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException("Template billers not found.");
        }

        return $path;
    }

    /**
     * Export billers to file.
     *
     * @param array<int> $ids
     * @param string $format 'excel' or 'pdf'
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters Optional date filters for created_at
     * @return string Relative file path.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'billers_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new BillersExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
