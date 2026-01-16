<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Product\ProductIndexRequest;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProductController
 *
 * API controller for managing products with full CRUD operations, variants, batches,
 * warehouses, images, and comprehensive business logic.
 */
class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ProductService $service
     */
    public function __construct(
        private readonly ProductService $service
    ) {
    }

    /**
     * Display a paginated listing of products.
     *
     * @param ProductIndexRequest $request
     * @return JsonResponse
     */
    public function index(ProductIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        // Handle imeiorvariant filter
        if (isset($filters['imeiorvariant'])) {
            if ($filters['imeiorvariant'] === 'imei') {
                $filters['is_imei'] = '1';
            } elseif ($filters['imeiorvariant'] === 'variant') {
                $filters['is_variant'] = '1';
            }
            unset($filters['imeiorvariant']);
        }

        $products = $this->service->getProducts($filters, $perPage)
            ->through(fn($product) => new ProductResource($product));

        return response()->success($products, 'Products fetched successfully');
    }

    /**
     * Store a newly created product.
     *
     * @param ProductRequest $request
     * @return JsonResponse
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->service->createProduct($request->validated());

        return response()->success(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $product = $this->service->getProduct($product->id);

        return response()->success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * Update the specified product.
     *
     * @param ProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->service->updateProduct($product, $request->validated());

        return response()->success(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product from storage (soft delete).
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->service->deleteProduct($product);

        return response()->success(null, 'Product deleted successfully');
    }

    /**
     * Bulk delete multiple products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:products,id'],
        ]);

        $count = $this->service->bulkDeleteProducts($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} product" . ($count !== 1 ? 's' : '') . " successfully"
        );
    }

    /**
     * Get products without variants.
     *
     * @return JsonResponse
     */
    public function getProductsWithoutVariant(): JsonResponse
    {
        $products = $this->service->getProductsWithoutVariant();

        return response()->success($products, 'Products without variants fetched successfully');
    }

    /**
     * Get products with variants.
     *
     * @return JsonResponse
     */
    public function getProductsWithVariant(): JsonResponse
    {
        $products = $this->service->getProductsWithVariant();

        return response()->success($products, 'Products with variants fetched successfully');
    }

    /**
     * Generate a unique product code.
     *
     * @return JsonResponse
     */
    public function generateCode(): JsonResponse
    {
        $code = $this->service->generateCode();

        return response()->success(
            ['code' => $code],
            'Product code generated successfully'
        );
    }

    /**
     * Import products from a file.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importProducts($request->file('file'));

        return response()->success(null, 'Products imported successfully');
    }

    /**
     * Reorder product images.
     *
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function reorderImages(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'image_urls' => ['required', 'array', 'min:1'],
            'image_urls.*' => ['required', 'string'],
        ]);

        $product = $this->service->reorderImages($product, $validated['image_urls']);

        return response()->success(
            new ProductResource($product),
            'Product images reordered successfully'
        );
    }

    /**
     * Search products by name or code (for related products and extras).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'term' => ['required', 'string', 'min:3'],
        ]);

        $products = $this->service->searchProducts($request->input('term'));

        return response()->success($products, 'Products found successfully');
    }

    /**
     * Get sale and purchase units based on base unit ID.
     *
     * @param int $unitId Base unit ID
     * @return JsonResponse
     */
    public function getSaleUnits(int $unitId): JsonResponse
    {
        $units = $this->service->getSaleUnits($unitId);

        return response()->success($units, 'Sale units fetched successfully');
    }

    /**
     * Search product for combo products table.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchComboProduct(Request $request): JsonResponse
    {
        $request->validate([
            'data' => ['required', 'string'],
        ]);

        $result = $this->service->searchComboProduct($request->input('data'));

        if ($result === null) {
            return response()->error('Product not found', 404);
        }

        return response()->success([$result], 'Product found successfully');
    }
}

