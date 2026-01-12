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
 * BrandController
 *
 * API controller for managing brands with full CRUD operations.
 * Keeps controller thin with all business logic delegated to BrandService.
 */
class BrandController extends Controller
{
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
            $this->pluralizeMessage($count, 'Deleted {count} brand', 'successfully')
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
            $this->pluralizeMessage($count, 'Activated {count} brand', 'successfully')
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
            $this->pluralizeMessage($count, 'Deactivated {count} brand', 'successfully')
        );
    }

    /**
     * Export brands to Excel or PDF.
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

        $filePath = $this->service->exportBrands($ids, $format, $user, $columns, $method);

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
     * @param string $message Message with {count} placeholder (e.g., "Activated {count} brand")
     * @param string $suffix Suffix to append (e.g., "successfully")
     * @return string
     */
    private function pluralizeMessage(int $count, string $message, string $suffix): string
    {
        $pluralSuffix = $count !== 1 ? 's' : '';
        return str_replace('{count}', (string)$count, $message) . $pluralSuffix . " {$suffix}";
    }
}

