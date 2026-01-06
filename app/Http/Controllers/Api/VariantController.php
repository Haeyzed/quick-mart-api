<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VariantBulkDestroyRequest;
use App\Http\Requests\VariantIndexRequest;
use App\Http\Requests\VariantRequest;
use App\Http\Resources\VariantResource;
use App\Models\Variant;
use App\Services\VariantService;
use Illuminate\Http\JsonResponse;

/**
 * VariantController
 *
 * API controller for managing variants with full CRUD operations.
 */
class VariantController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param VariantService $service
     */
    public function __construct(
        private readonly VariantService $service
    )
    {
    }

    /**
     * Display a paginated listing of variants.
     *
     * @param VariantIndexRequest $request
     * @return JsonResponse
     */
    public function index(VariantIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $variants = $this->service->getVariants($filters, $perPage)
            ->through(fn($variant) => new VariantResource($variant));

        return response()->success($variants, 'Variants fetched successfully');
    }

    /**
     * Store a newly created variant.
     *
     * @param VariantRequest $request
     * @return JsonResponse
     */
    public function store(VariantRequest $request): JsonResponse
    {
        $variant = $this->service->createVariant($request->validated());

        return response()->success(
            new VariantResource($variant),
            'Variant created successfully',
            201
        );
    }

    /**
     * Display the specified variant.
     *
     * @param Variant $variant
     * @return JsonResponse
     */
    public function show(Variant $variant): JsonResponse
    {
        return response()->success(
            new VariantResource($variant),
            'Variant retrieved successfully'
        );
    }

    /**
     * Update the specified variant.
     *
     * @param VariantRequest $request
     * @param Variant $variant
     * @return JsonResponse
     */
    public function update(VariantRequest $request, Variant $variant): JsonResponse
    {
        $variant = $this->service->updateVariant($variant, $request->validated());

        return response()->success(
            new VariantResource($variant),
            'Variant updated successfully'
        );
    }

    /**
     * Remove the specified variant from storage.
     *
     * @param Variant $variant
     * @return JsonResponse
     */
    public function destroy(Variant $variant): JsonResponse
    {
        $this->service->deleteVariant($variant);

        return response()->success(null, 'Variant deleted successfully');
    }

    /**
     * Bulk delete multiple variants.
     *
     * @param VariantBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(VariantBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteVariants($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} variants successfully"
        );
    }
}
