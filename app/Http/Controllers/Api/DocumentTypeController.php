<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentTypes\DocumentTypeBulkActionRequest;
use App\Http\Requests\DocumentTypes\StoreDocumentTypeRequest;
use App\Http\Requests\DocumentTypes\UpdateDocumentTypeRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\DocumentTypeResource;
use App\Mail\ExportMail;
use App\Models\DocumentType;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\DocumentTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class DocumentTypeController
 *
 * API Controller for Document Type CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to DocumentTypeService.
 *
 * @tags HRM Management
 */
class DocumentTypeController extends Controller
{
    /**
     * DocumentTypeController constructor.
     */
    public function __construct(
        private readonly DocumentTypeService $service
    ) {}

    /**
     * List Document Types
     *
     * Display a paginated listing of document types. Supports search and active filter.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view document types')) {
            return response()->forbidden('Permission denied for viewing document types.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /**
                 * Search term to filter employees by name, email, phone, or staff ID.
                 *
                 * @example "Jane Doe"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter employees starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter employees up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            ]),
            /**
             * Amount of items per page.
             *
             * @example 50
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            DocumentTypeResource::collection($items),
            'Document types retrieved successfully'
        );
    }

    /**
     * Get Document Type Options
     *
     * Retrieve a lightweight list of active document types for dropdowns.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view document types')) {
            return response()->forbidden('Permission denied for viewing document type options.');
        }

        return response()->success(
            $this->service->getOptions(),
            'Document type options retrieved successfully'
        );
    }

    /**
     * Create Document Type
     */
    public function store(StoreDocumentTypeRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create document types')) {
            return response()->forbidden('Permission denied for create.');
        }

        $documentType = $this->service->create($request->validated());

        return response()->success(
            new DocumentTypeResource($documentType),
            'Document type created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Document Type
     */
    public function show(DocumentType $documentType): JsonResponse
    {
        if (auth()->user()->denies('view document types')) {
            return response()->forbidden('Permission denied for view.');
        }

        return response()->success(
            new DocumentTypeResource($documentType),
            'Document type details retrieved successfully'
        );
    }

    /**
     * Update Document Type
     */
    public function update(UpdateDocumentTypeRequest $request, DocumentType $documentType): JsonResponse
    {
        if (auth()->user()->denies('update document types')) {
            return response()->forbidden('Permission denied for update.');
        }

        $updatedDocumentType = $this->service->update($documentType, $request->validated());

        return response()->success(
            new DocumentTypeResource($updatedDocumentType),
            'Document type updated successfully'
        );
    }

    /**
     * Delete Document Type
     */
    public function destroy(DocumentType $documentType): JsonResponse
    {
        if (auth()->user()->denies('delete document types')) {
            return response()->forbidden('Permission denied for delete.');
        }

        $this->service->delete($documentType);

        return response()->success(null, 'Document type deleted successfully');
    }

    /**
     * Bulk Delete Document Types
     */
    public function bulkDestroy(DocumentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete document types')) {
            return response()->forbidden('Permission denied for bulk delete.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} document types"
        );
    }

    /**
     * Bulk Activate Document Types
     */
    public function bulkActivate(DocumentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update document types')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} document types activated"
        );
    }

    /**
     * Bulk Deactivate Document Types
     */
    public function bulkDeactivate(DocumentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update document types')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} document types deactivated"
        );
    }

    /**
     * Import Document Types
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import document types')) {
            return response()->forbidden('Permission denied for import.');
        }

        $this->service->import($request->file('file'));

        return response()->success(null, 'Document types imported successfully');
    }

    /**
     * Export Document Types
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export document types')) {
            return response()->forbidden('Permission denied for export.');
        }

        $validated = $request->validated();
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()->download(Storage::disk('public')->path($path))->deleteFileAfterSend();
        }

        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (!$user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();
            if (!$mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'document_types_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Document Types Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(null, 'Export is being processed and will be sent to email: ' . $user->email);
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download Import Template
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import document types')) {
            return response()->forbidden('Permission denied for downloading template.');
        }

        $path = $this->service->download();

        return response()->download($path, basename($path), ['Content-Type' => 'text/csv']);
    }
}
