<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\TaxBulkDestroyRequest;
use App\Http\Requests\TaxBulkUpdateRequest;
use App\Http\Requests\TaxIndexRequest;
use App\Http\Requests\TaxRequest;
use App\Http\Resources\TaxResource;
use App\Models\Tax;
use App\Models\User;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * TaxController
 *
 * API controller for managing taxes with full CRUD operations.
 * Keeps controller thin with all business logic delegated to TaxService.
 */
class TaxController extends Controller
{
    public function __construct(
        private readonly TaxService $service
    )
    {
    }

    /**
     * Display a paginated listing of taxes.
     *
     * @param TaxIndexRequest $request
     * @return JsonResponse
     */
    public function index(TaxIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $taxes = $this->service->getTaxes($filters, $perPage)
            ->through(fn($tax) => new TaxResource($tax));

        return response()->success($taxes, 'Taxes fetched successfully');
    }

    /**
     * Store a newly created tax.
     *
     * @param TaxRequest $request
     * @return JsonResponse
     */
    public function store(TaxRequest $request): JsonResponse
    {
        $tax = $this->service->createTax($request->validated());

        return response()->success(
            new TaxResource($tax),
            'Tax created successfully',
            201
        );
    }

    /**
     * Display the specified tax.
     *
     * @param Tax $tax
     * @return JsonResponse
     */
    public function show(Tax $tax): JsonResponse
    {
        return response()->success(
            new TaxResource($tax),
            'Tax retrieved successfully'
        );
    }

    /**
     * Update the specified tax.
     *
     * @param TaxRequest $request
     * @param Tax $tax
     * @return JsonResponse
     */
    public function update(TaxRequest $request, Tax $tax): JsonResponse
    {
        $tax = $this->service->updateTax($tax, $request->validated());

        return response()->success(
            new TaxResource($tax),
            'Tax updated successfully'
        );
    }

    /**
     * Remove the specified tax from storage.
     *
     * @param Tax $tax
     * @return JsonResponse
     */
    public function destroy(Tax $tax): JsonResponse
    {
        $this->service->deleteTax($tax);

        return response()->success(null, 'Tax deleted successfully');
    }

    /**
     * Bulk delete multiple taxes.
     *
     * @param TaxBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(TaxBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteTaxes($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            $this->pluralizeMessage($count, 'Deleted {count} tax', 'successfully')
        );
    }

    /**
     * Import taxes from a file.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importTaxes($request->file('file'));

        return response()->success(null, 'Taxes imported successfully');
    }

    /**
     * Bulk activate multiple taxes.
     *
     * @param TaxBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(TaxBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateTaxes($request->validated()['ids']);

        return response()->success(
            ['activated_count' => $count],
            $this->pluralizeMessage($count, 'Activated {count} tax', 'successfully')
        );
    }

    /**
     * Bulk deactivate multiple taxes.
     *
     * @param TaxBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(TaxBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateTaxes($request->validated()['ids']);

        return response()->success(
            ['deactivated_count' => $count],
            $this->pluralizeMessage($count, 'Deactivated {count} tax', 'successfully')
        );
    }

    /**
     * Export taxes to Excel or PDF.
     *
     * @param ExportRequest $request
     * @return JsonResponse|Response
     */
    public function export(ExportRequest $request): JsonResponse|Response
    {
        $validated = $request->validated();
        $ids = $validated['ids'] ?? [];
        $format = $validated['format'];
        $method = $validated['method'];
        $columns = $validated['columns'] ?? [];
        $user = $method === 'email' ? User::findOrFail($validated['user_id']) : null;

        $filePath = $this->service->exportTaxes($ids, $format, $user, $columns, $method);

        if ($method === 'download') {
            return Storage::disk('public')->download($filePath);
        }

        return response()->success(null, 'Export file sent via email successfully');
    }

    /**
     * Pluralize message with count.
     *
     * Utility helper to eliminate string concatenation duplication across bulk methods.
     *
     * @param int $count
     * @param string $message Message with {count} placeholder (e.g., "Activated {count} tax")
     * @param string $suffix Suffix to append (e.g., "successfully")
     * @return string
     */
    private function pluralizeMessage(int $count, string $message, string $suffix): string
    {
        $pluralSuffix = $count !== 1 ? 'es' : '';
        return str_replace('{count}', (string)$count, $message) . $pluralSuffix . " {$suffix}";
    }
}

