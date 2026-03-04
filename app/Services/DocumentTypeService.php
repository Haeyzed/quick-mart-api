<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\DocumentTypesExport;
use App\Imports\DocumentTypesImport;
use App\Models\DocumentType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class DocumentTypeService
 *
 * Handles all core business logic and database interactions for Document Types.
 * Acts as the intermediary between the controllers and the database layer.
 */
class DocumentTypeService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated document types based on filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator<DocumentType>
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return DocumentType::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get options list for dropdowns.
     *
     * @return Collection<int, array{value: int, label: string, requires_expiry: bool}>
     */
    public function getOptions(): Collection
    {
        return DocumentType::active()
            ->select('id', 'name', 'code', 'requires_expiry')
            ->orderBy('name')
            ->get()
            ->map(fn(DocumentType $type) => [
                'value' => $type->id,
                'label' => $type->name,
                'requires_expiry' => $type->requires_expiry,
            ]);
    }

    /**
     * Create a new document type.
     *
     * @param array<string, mixed> $data
     * @return DocumentType
     */
    public function create(array $data): DocumentType
    {
        return DB::transaction(fn() => DocumentType::query()->create($data));
    }

    /**
     * Bulk delete multiple document types.
     *
     * @param array<int> $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn() => DocumentType::query()->whereIn('id', $ids)->delete());
    }

    /**
     * Delete a document type.
     *
     * @param DocumentType $documentType
     * @return void
     */
    public function delete(DocumentType $documentType): void
    {
        DB::transaction(fn() => $documentType->delete());
    }

    /**
     * Bulk update active status for document types.
     *
     * @param array<int> $ids
     * @param bool $isActive
     * @return int
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return DB::transaction(fn() => DocumentType::query()->whereIn('id', $ids)->update(['is_active' => $isActive]));
    }

    /**
     * Update an existing document type.
     *
     * @param DocumentType $documentType
     * @param array<string, mixed> $data
     * @return DocumentType
     */
    public function update(DocumentType $documentType, array $data): DocumentType
    {
        return DB::transaction(function () use ($documentType, $data) {
            $documentType->update($data);

            return $documentType->fresh();
        });
    }

    /**
     * Import multiple document types from an uploaded file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function import(UploadedFile $file): void
    {
        ExcelFacade::import(new DocumentTypesImport, $file);
    }

    /**
     * Download a document types CSV template.
     *
     * @return string
     * @throws RuntimeException
     */
    public function download(): string
    {
        $fileName = 'document-types-sample.csv';
        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException('Template for document types not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing document type data.
     *
     * @param array<int> $ids
     * @param string $format
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters
     * @return string
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'document_types_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new DocumentTypesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
