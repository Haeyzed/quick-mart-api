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
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class BrandController
 *
 * Handles Brand lifecycle operations including bulk actions,
 * imports, and multi-format exports.
 *
 * @group Brand Management
 */
class BrandController extends Controller
{
    /**
     * BrandController constructor.
     *
     * @param BrandService $service
     */
    public function __construct(
        private readonly BrandService $service
    ) {}

    /**
     * Display a listing of brands.
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

        // Transform data while preserving LengthAwarePaginator for the Response Macro
        $brands->through(fn (Brand $brand) => new BrandResource($brand));

        return response()->success(
            $brands,
            'Brands fetched successfully'
        );
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
     * Update the specified brand.
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
     * Remove the specified brand.
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
     * Bulk delete brands.
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
     * Bulk activate brands.
     *
     * @param BrandBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateBrands($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} brands activated");
    }

    /**
     * Bulk deactivate brands.
     *
     * @param BrandBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateBrands($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} brands deactivated");
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
     * Export brands.
     *
     * @param ExportRequest $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();
        
        // Resolve user safely (assuming Sanctum/Passport auth is active, or passed ID)
        $user = ($validated['method'] === 'email') 
            ? User::findOrFail($validated['user_id']) 
            : null;

        $filePath = $this->service->exportBrands(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return response()->download(
                \Illuminate\Support\Facades\Storage::disk('public')->path($filePath)
            );
        }

        return response()->success(null, 'Export processed and sent via email');
    }
}