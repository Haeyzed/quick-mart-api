<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brands\BrandBulkDestroyRequest;
use App\Http\Requests\Brands\BrandBulkUpdateRequest;
use App\Http\Requests\Brands\BrandIndexRequest;
use App\Http\Requests\Brands\BrandRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Models\User;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Class BrandController
 * * Handles high-performance Brand lifecycle operations including bulk actions, 
 * imports, and multi-format exports.
 * * @group Brand Management
 */
class BrandController extends Controller
{
    /**
     * @param BrandService $service
     */
    public function __construct(
        private readonly BrandService $service
    ) {}

    /**
     * Display a paginated listing of brands with filters.
     *
     * @param BrandIndexRequest $request
     * @return JsonResponse
     */
    public function index(BrandIndexRequest $request): JsonResponse
    {
        $brands = $this->service->getBrands(
            $request->validated(), 
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            BrandResource::collection($brands)->resource,
            'Brands fetched successfully'
        );
    }

    /**
     * Store a newly created brand with automatic slug generation.
     *
     * @param BrandRequest $request
     * @return JsonResponse
     */
    public function store(BrandRequest $request): JsonResponse
    {
        $brand = $this->service->createBrand($request->validated());

        return response()->success(
            new BrandResource($brand),
            'Brand created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified brand.
     *
     * @param Brand $brand
     * @return JsonResponse
     */
    public function show(Brand $brand): JsonResponse
    {
        return response()->success(
            new BrandResource($brand),
            'Brand retrieved successfully'
        );
    }

    /**
     * Update the specified brand and manage file cleanup.
     *
     * @param BrandRequest $request
     * @param Brand $brand
     * @return JsonResponse
     */
    public function update(BrandRequest $request, Brand $brand): JsonResponse
    {
        $updatedBrand = $this->service->updateBrand($brand, $request->validated());

        return response()->success(
            new BrandResource($updatedBrand),
            'Brand updated successfully'
        );
    }

    /**
     * Remove the specified brand (checks for product constraints).
     *
     * @param Brand $brand
     * @return JsonResponse
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $this->service->deleteBrand($brand);

        return response()->success(null, 'Brand deleted successfully');
    }

    /**
     * Bulk delete brands efficiently.
     *
     * @param BrandBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(BrandBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteBrands($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} brands"
        );
    }

    /**
     * Bulk import brands from CSV/Excel using batch processing.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importBrands($request->file('file'));

        return response()->success(null, 'Brands imported successfully');
    }

    /**
     * Activate multiple brands in a single database trip.
     * * @param BrandBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateBrands($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} brands activated");
    }

    /**
     * Deactivate multiple brands in a single database trip.
     * * @param BrandBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateBrands($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} brands deactivated");
    }

    /**
     * Export brands to Excel/PDF with optional email delivery.
     *
     * @param ExportRequest $request
     * @return JsonResponse|Response
     */
    public function export(ExportRequest $request): JsonResponse|Response
    {
        $validated = $request->validated();
        $user = ($validated['method'] === 'email') ? User::findOrFail($validated['user_id']) : null;

        $filePath = $this->service->exportBrands(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return Storage::disk('public')->download($filePath);
        }

        return response()->success(null, 'Export sent via email');
    }
}