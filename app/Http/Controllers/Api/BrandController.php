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
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Brand CRUD and bulk operations.
 *
 * Handles index, store, show, update, destroy, bulk activate/deactivate/destroy,
 * import, and export. All responses use the ResponseServiceProvider macros.
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
     * Display a paginated listing of brands.
     *
     * @param BrandIndexRequest $request Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated brands with meta and links.
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
     * @param BrandRequest $request Validated brand attributes.
     * @return JsonResponse Created brand with 201 status.
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
     * Requires brands-index permission. Returns the brand as a resource.
     *
     * @param Brand $brand The brand instance resolved via route model binding.
     * @return JsonResponse
     */
    public function show(Brand $brand): JsonResponse
    {
        $brand = $this->service->getBrand($brand);

        return response()->success(
            new BrandResource($brand),
            'Brand retrieved successfully'
        );
    }

    /**
     * Update the specified brand.
     *
     * @param BrandRequest $request Validated brand attributes.
     * @param Brand $brand The brand instance to update.
     * @return JsonResponse Updated brand.
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
     * Remove the specified brand (soft delete).
     *
     * @param Brand $brand The brand instance to delete.
     * @return JsonResponse Success message.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $this->service->deleteBrand($brand);

        return response()->success(null, 'Brand deleted successfully');
    }

    /**
     * Bulk delete brands (soft delete). Skips brands with products.
     *
     * @param BrandBulkDestroyRequest $request Validated ids array.
     * @return JsonResponse Deleted count and message.
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
     * Bulk activate brands by ID.
     *
     * @param BrandBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Activated count and message.
     */
    public function bulkActivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateBrands($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} brands activated");
    }

    /**
     * Bulk deactivate brands by ID.
     *
     * @param BrandBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Deactivated count and message.
     */
    public function bulkDeactivate(BrandBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateBrands($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} brands deactivated");
    }

    /**
     * Import brands from Excel/CSV file.
     *
     * @param ImportRequest $request Validated file upload.
     * @return JsonResponse Success message.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importBrands($request->file('file'));

        return response()->success(null, 'Brands imported successfully');
    }

    /**
     * Export brands to Excel or PDF.
     *
     * Supports download or email delivery based on method.
     *
     * @param ExportRequest $request Validated export params: ids, format, method, columns, user_id (if email).
     * @return JsonResponse|BinaryFileResponse Success message or file download.
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
                Storage::disk('public')->path($filePath)
            );
        }

        return response()->success(null, 'Export processed and sent via email');
    }
}