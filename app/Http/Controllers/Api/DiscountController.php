<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountBulkDestroyRequest;
use App\Http\Requests\DiscountIndexRequest;
use App\Http\Requests\DiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;

/**
 * DiscountController
 *
 * API controller for managing discounts with full CRUD operations.
 */
class DiscountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param DiscountService $service
     */
    public function __construct(
        private readonly DiscountService $service
    )
    {
    }

    /**
     * Display a paginated listing of discounts.
     *
     * @param DiscountIndexRequest $request
     * @return JsonResponse
     */
    public function index(DiscountIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $discounts = $this->service->getDiscounts($filters, $perPage)
            ->through(fn($discount) => new DiscountResource($discount));

        return response()->success($discounts, 'Discounts fetched successfully');
    }

    /**
     * Store a newly created discount.
     *
     * @param DiscountRequest $request
     * @return JsonResponse
     */
    public function store(DiscountRequest $request): JsonResponse
    {
        $discount = $this->service->createDiscount($request->validated());

        return response()->success(
            new DiscountResource($discount),
            'Discount created successfully',
            201
        );
    }

    /**
     * Display the specified discount.
     *
     * @param Discount $discount
     * @return JsonResponse
     */
    public function show(Discount $discount): JsonResponse
    {
        return response()->success(
            new DiscountResource($discount),
            'Discount retrieved successfully'
        );
    }

    /**
     * Update the specified discount.
     *
     * @param DiscountRequest $request
     * @param Discount $discount
     * @return JsonResponse
     */
    public function update(DiscountRequest $request, Discount $discount): JsonResponse
    {
        $discount = $this->service->updateDiscount($discount, $request->validated());

        return response()->success(
            new DiscountResource($discount),
            'Discount updated successfully'
        );
    }

    /**
     * Remove the specified discount from storage.
     *
     * @param Discount $discount
     * @return JsonResponse
     */
    public function destroy(Discount $discount): JsonResponse
    {
        $this->service->deleteDiscount($discount);

        return response()->success(null, 'Discount deleted successfully');
    }

    /**
     * Bulk delete multiple discounts.
     *
     * @param DiscountBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DiscountBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteDiscounts($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} discounts successfully"
        );
    }

    /**
     * Search for a product by code.
     *
     * @param string $code Product code
     * @return JsonResponse
     */
    public function productSearch(string $code): JsonResponse
    {
        $product = $this->service->productSearch($code);

        if (!$product) {
            return response()->error('Product not found', 404);
        }

        return response()->success(
            [
                'id' => $product[0],
                'name' => $product[1],
                'code' => $product[2],
            ],
            'Product found successfully'
        );
    }
}
