<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brands\BrandBulkDestroyRequest;
use App\Http\Requests\Brands\BrandBulkUpdateRequest;
use App\Http\Requests\Brands\BrandIndexRequest;
use App\Http\Requests\Brands\BrandRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;

/**
 * BrandController
 *
 * API controller for managing brands with full CRUD operations.
 */
class BrandController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param BrandService $service
     */
    public function __construct(
        private readonly BrandService $service
    )
    {
    }

    /**
     * Display a paginated listing of brands.
     *
     * @param BrandIndexRequest $request
     * @return JsonResponse
     */
    public function index(BrandIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $brands = $this->service->getBrands($filters, $perPage)
            ->through(fn($brand) => new BrandResource($brand));

        return response()->success($brands, 'Brands fetched successfully');
    }

    /**
     * Store a newly created brand.
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
            201
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
     * Update the specified brand.
     *
     * @param BrandRequest $request
     * @param Brand $brand
     * @return JsonResponse
     */
    public function update(BrandRequest $request, Brand $brand): JsonResponse
    {
        $brand = $this->service->updateBrand($brand, $request->validated());

        return response()->success(
            new BrandResource($brand),
            'Brand updated successfully'
        );
    }

    /**
     * Remove the specified brand from storage.
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
     * Bulk delete multiple brands.
     *
     * @param BrandBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(BrandBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteBrands($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} brands successfully"
        );
    }

    /**
     * Import brands from a file.
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
     * Bulk activate multiple brands.
     *
     * @param BrandBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateBrands($request->validated()['ids']);

        return response()->success(
            ['activated_count' => $count],
            "Activated {$count} brand" . ($count !== 1 ? 's' : '') . " successfully"
        );
    }

    /**
     * Bulk deactivate multiple brands.
     *
     * @param BrandBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateBrands($request->validated()['ids']);

        return response()->success(
            ['deactivated_count' => $count],
            "Deactivated {$count} brand" . ($count !== 1 ? 's' : '') . " successfully"
        );
    }
}

