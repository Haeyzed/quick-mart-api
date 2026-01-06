<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\TaxBulkDestroyRequest;
use App\Http\Requests\TaxIndexRequest;
use App\Http\Requests\TaxRequest;
use App\Http\Resources\TaxResource;
use App\Models\Tax;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;

/**
 * TaxController
 *
 * API controller for managing taxes with full CRUD operations.
 */
class TaxController extends Controller
{
    /**
     * Create a new controller instance.
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
            "Deleted {$count} taxes successfully"
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
}

