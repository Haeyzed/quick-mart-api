<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Taxes\TaxBulkDestroyRequest;
use App\Http\Requests\Taxes\TaxBulkUpdateRequest;
use App\Http\Requests\Taxes\TaxIndexRequest;
use App\Http\Requests\Taxes\TaxRequest;
use App\Http\Resources\TaxResource;
use App\Models\Tax;
use App\Models\User;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Tax CRUD and bulk operations.
 *
 * Handles index, store, show, update, destroy, bulk activate/deactivate/destroy,
 * import, and export. All responses use the ResponseServiceProvider macros.
 *
 * @group Tax Management
 */
class TaxController extends Controller
{
    /**
     * TaxController constructor.
     *
     * @param TaxService $service
     */
    public function __construct(
        private readonly TaxService $service
    )
    {
    }

    /**
     * Display a paginated listing of taxes.
     *
     * @param TaxIndexRequest $request Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated taxes with meta and links.
     */
    public function index(TaxIndexRequest $request): JsonResponse
    {
        $taxes = $this->service->getTaxes(
            $request->validated(),
            (int)$request->input('per_page', 10)
        );

        $taxes->through(fn(Tax $tax) => new TaxResource($tax));

        return response()->success(
            $taxes,
            'Taxes fetched successfully'
        );
    }

    /**
     * Store a newly created tax.
     *
     * @param TaxRequest $request Validated tax attributes.
     * @return JsonResponse Created tax with 201 status.
     */
    public function store(TaxRequest $request): JsonResponse
    {
        $tax = $this->service->createTax($request->validated());

        return response()->success(
            new TaxResource($tax),
            'Tax created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified tax.
     *
     * Requires taxes-index permission. Returns the tax as a resource.
     *
     * @param Tax $tax The tax instance resolved via route model binding.
     * @return JsonResponse
     */
    public function show(Tax $tax): JsonResponse
    {
        $tax = $this->service->getTax($tax);

        return response()->success(
            new TaxResource($tax),
            'Tax retrieved successfully'
        );
    }

    /**
     * Update the specified tax.
     *
     * @param TaxRequest $request Validated tax attributes.
     * @param Tax $tax The tax instance to update.
     * @return JsonResponse Updated tax.
     */
    public function update(TaxRequest $request, Tax $tax): JsonResponse
    {
        $updatedTax = $this->service->updateTax($tax, $request->validated());

        return response()->success(
            new TaxResource($updatedTax),
            'Tax updated successfully'
        );
    }

    /**
     * Remove the specified tax (soft delete).
     *
     * @param Tax $tax The tax instance to delete.
     * @return JsonResponse Success message.
     */
    public function destroy(Tax $tax): JsonResponse
    {
        $this->service->deleteTax($tax);

        return response()->success(null, 'Tax deleted successfully');
    }

    /**
     * Bulk delete taxes (soft delete). Skips taxes with products.
     *
     * @param TaxBulkDestroyRequest $request Validated ids array.
     * @return JsonResponse Deleted count and message.
     */
    public function bulkDestroy(TaxBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteTaxes($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} taxes"
        );
    }

    /**
     * Bulk activate taxes by ID.
     *
     * @param TaxBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Activated count and message.
     */
    public function bulkActivate(TaxBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateTaxes($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} taxes activated");
    }

    /**
     * Bulk deactivate taxes by ID.
     *
     * @param TaxBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Deactivated count and message.
     */
    public function bulkDeactivate(TaxBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateTaxes($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} taxes deactivated");
    }

    /**
     * Import taxes from Excel/CSV file.
     *
     * @param ImportRequest $request Validated file upload.
     * @return JsonResponse Success message.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importTaxes($request->file('file'));

        return response()->success(null, 'Taxes imported successfully');
    }

    /**
     * Export taxes to Excel or PDF.
     *
     * Supports download or email delivery based on method.
     *
     * @param ExportRequest $request Validated export params: ids, format, method, columns, user_id (if email).
     * @return JsonResponse|BinaryFileResponse Success message or file download.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();

        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportTaxes(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return response()->download(
                Storage::disk('public')->path($filePath)
            );
        }

        return response()->success(null, 'Export processed and sent via email');
    }
}
