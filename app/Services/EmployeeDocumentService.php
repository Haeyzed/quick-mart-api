<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmployeeDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles business logic and persistence for employee documents:
 * listing with filters, create/update/delete with file upload and cleanup.
 */
class EmployeeDocumentService
{
    private const DOCUMENT_PATH = 'documents/employees';

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated employee documents with optional filters.
     *
     * @param  array<string, mixed>  $filters  e.g. employee_id, document_type_id, expired, start_date, end_date
     * @return LengthAwarePaginator<EmployeeDocument>
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return EmployeeDocument::query()
            ->with(['documentType', 'employee:id,name,employee_code'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create an employee document; uploads file when present in $data.
     *
     * @param  array<string, mixed>  $data  Validated data; may include 'file' (UploadedFile).
     */
    public function create(array $data): EmployeeDocument
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);

            return EmployeeDocument::query()->create($data);
        });
    }

    /**
     * Update an employee document; replaces file when new one is provided.
     *
     * @param  array<string, mixed>  $data  Validated data; may include 'file' (UploadedFile).
     */
    public function update(EmployeeDocument $document, array $data): EmployeeDocument
    {
        return DB::transaction(function () use ($document, $data) {
            $data = $this->handleUploads($data, $document);
            $document->update($data);

            return $document->fresh(['documentType', 'employee']);
        });
    }

    /**
     * Delete an employee document and its stored file.
     */
    public function delete(EmployeeDocument $document): void
    {
        DB::transaction(function () use ($document) {
            $this->cleanupFiles($document);
            $document->delete();
        });
    }

    /**
     * Process file upload when 'file' is an UploadedFile; sets file_path and file_url, removes 'file' key.
     *
     * @param  array<string, mixed>  $data
     * @param  EmployeeDocument|null  $document  Existing document when updating (old file is deleted).
     * @return array<string, mixed>
     */
    private function handleUploads(array $data, ?EmployeeDocument $document = null): array
    {
        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            if ($document?->file_path) {
                $this->uploadService->delete($document->file_path);
            }
            $path = $this->uploadService->upload($data['file'], self::DOCUMENT_PATH);
            $data['file_path'] = $path;
            $data['file_url'] = $this->uploadService->url($path) ?? '';
            unset($data['file']);
        }

        return $data;
    }

    /**
     * Remove stored file for the given document when file_path is set.
     */
    private function cleanupFiles(EmployeeDocument $document): void
    {
        if ($document->file_path) {
            $this->uploadService->delete($document->file_path);
        }
    }
}
