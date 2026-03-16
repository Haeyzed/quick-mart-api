<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\ProductBulkActionRequest;
use App\Http\Requests\Products\ProductFilterRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class ProductController
 *
 * API Controller for Product CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to ProductService.
 *
 * @tags Product Management
 */
class ProductController extends Controller
{
    /**
     * ProductController constructor.
     */
    public function __construct(
        private readonly ProductService $service
    ) {}

    /**
     * List Products
     *
     * Display a paginated listing of products. Supports searching and advanced filtering.
     */
    public function index(ProductFilterRequest $request): JsonResponse
    {
        if (auth()->user()->denies('view products')) {
            return response()->forbidden('Permission denied for viewing products list.');
        }

        $products = $this->service->getPaginated(
            $request->validated(),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }

    /**
     * Create Product
     *
     * Store a newly created product in the system.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create products')) {
            return response()->forbidden('Permission denied for creating a product.');
        }

        $product = $this->service->create($request->validated());

        return response()->success(
            new ProductResource($product),
            'Product created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Product
     *
     * Retrieve the details of a specific product by its ID.
     */
    public function show(Product $product): JsonResponse
    {
        if (auth()->user()->denies('view products')) {
            return response()->forbidden('Permission denied for viewing product details.');
        }

        return response()->success(
            new ProductResource($this->service->get($product)),
            'Product details retrieved successfully'
        );
    }

    /**
     * Update Product
     *
     * Update the specified product's information.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('update products')) {
            return response()->forbidden('Permission denied for updating a product.');
        }

        $updatedProduct = $this->service->update($product, $request->validated());

        return response()->success(
            new ProductResource($updatedProduct),
            'Product updated successfully'
        );
    }

    /**
     * Delete Product
     *
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        if (auth()->user()->denies('delete products')) {
            return response()->forbidden('Permission denied for deleting a product.');
        }

        $this->service->delete($product);

        return response()->success(null, 'Product deleted successfully');
    }

    /**
     * Bulk Delete Products
     *
     * Delete multiple products simultaneously using an array of IDs.
     */
    public function bulkDestroy(ProductBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete products')) {
            return response()->forbidden('Permission denied for bulk delete products.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} products"
        );
    }

    /**
     * Get Products Without Variant.
     */
    public function getProductsWithoutVariant(): JsonResponse
    {
        if (auth()->user()->denies('view products')) {
            return response()->forbidden('Permission denied for viewing products.');
        }

        return response()->success(
            $this->service->getProductsWithoutVariant(),
            'Products without variants retrieved successfully'
        );
    }

    /**
     * Get Products With Variant.
     */
    public function getProductsWithVariant(): JsonResponse
    {
        if (auth()->user()->denies('view products')) {
            return response()->forbidden('Permission denied for viewing products.');
        }

        return response()->success(
            $this->service->getProductsWithVariant(),
            'Products with variants retrieved successfully'
        );
    }

    /**
     * Generate unique product code.
     */
    public function generateCode(): JsonResponse
    {
        if (auth()->user()->denies('create products')) {
            return response()->forbidden('Permission denied for creating a product.');
        }

        return response()->success(
            ['code' => $this->service->generateCode()],
            'Product code generated successfully'
        );
    }

    /**
     * Import multiple products from an uploaded file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import products')) {
            return response()->forbidden('Permission denied for importing products.');
        }

        $this->service->import($request->file('file'));

        return response()->success(null, 'Products imported successfully');
    }

    /**
     * Reorder multiple images for a product.
     */
    public function reorderImages(Request $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('update products')) {
            return response()->forbidden('Permission denied for updating a product.');
        }

        $validated = $request->validate([
            'image_urls' => ['required', 'array'],
            'image_urls.*' => ['string'],
        ]);

        $updatedProduct = $this->service->reorderImages($product, $validated['image_urls']);

        return response()->success(
            new ProductResource($updatedProduct),
            'Product images reordered successfully'
        );
    }

    /**
     * Search products by keyword (name, code, variant item_code).
     */
    public function search(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view products')) {
            return response()->forbidden('Permission denied for viewing products.');
        }

        $keyword = $request->input('keyword');
        $warehouseId = $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null;

        if (!$keyword) {
            return response()->success([], 'Please provide a keyword to search.');
        }

        return response()->success(
            ProductResource::collection($this->service->search((string)$keyword, $warehouseId)),
            'Products searched successfully'
        );
    }

    /**
     * Get sale units based on base unit ID.
     */
    public function getSaleUnits(int $unitId): JsonResponse
    {
        if (auth()->user()->denies('view products')) {
            return response()->forbidden('Permission denied for viewing products.');
        }

        return response()->success(
            $this->service->getSaleUnits($unitId),
            'Sale units retrieved successfully'
        );
    }

    /**
     * Search combo products.
     */
    public function searchComboProduct(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view products')) {
            return response()->forbidden('Permission denied for viewing products.');
        }

        $keyword = $request->input('keyword');

        if (!$keyword) {
            return response()->success([], 'Please provide a keyword to search.');
        }

        // Just use the standard search for combo products too, since it filters by active standard products
        // Alternatively, you could add a specific scope if combo product searching needs different logic
        return response()->success(
            ProductResource::collection($this->service->search((string)$keyword, null)),
            'Combo products searched successfully'
        );
    }

    /**
     * Get product sale history.
     */
    public function saleHistory(Request $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('view product history')) {
            return response()->forbidden('Permission denied for viewing product history.');
        }

        $filters = $request->only(['warehouse_id', 'start_date', 'end_date']);
        $perPage = $request->integer('per_page', config('app.per_page'));

        $history = $this->service->getSaleHistory($product, $filters, $perPage);

        return response()->success($history, 'Product sale history retrieved successfully');
    }

    /**
     * Get product purchase history.
     */
    public function purchaseHistory(Request $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('view product history')) {
            return response()->forbidden('Permission denied for viewing product history.');
        }

        $filters = $request->only(['warehouse_id', 'start_date', 'end_date']);
        $perPage = $request->integer('per_page', config('app.per_page'));

        $history = $this->service->getPurchaseHistory($product, $filters, $perPage);

        return response()->success($history, 'Product purchase history retrieved successfully');
    }

    /**
     * Get product sale return history.
     */
    public function saleReturnHistory(Request $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('view product history')) {
            return response()->forbidden('Permission denied for viewing product history.');
        }

        $filters = $request->only(['warehouse_id', 'start_date', 'end_date']);
        $perPage = $request->integer('per_page', config('app.per_page'));

        $history = $this->service->getSaleReturnHistory($product, $filters, $perPage);

        return response()->success($history, 'Product sale return history retrieved successfully');
    }

    /**
     * Get product purchase return history.
     */
    public function purchaseReturnHistory(Request $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('view product history')) {
            return response()->forbidden('Permission denied for viewing product history.');
        }

        $filters = $request->only(['warehouse_id', 'start_date', 'end_date']);
        $perPage = $request->integer('per_page', config('app.per_page'));

        $history = $this->service->getPurchaseReturnHistory($product, $filters, $perPage);

        return response()->success($history, 'Product purchase return history retrieved successfully');
    }

    /**
     * Get product adjustment history.
     */
    public function adjustmentHistory(Request $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('view product history')) {
            return response()->forbidden('Permission denied for viewing product history.');
        }

        $filters = $request->only(['warehouse_id', 'start_date', 'end_date']);
        $perPage = $request->integer('per_page', config('app.per_page'));

        $history = $this->service->getAdjustmentHistory($product, $filters, $perPage);

        return response()->success($history, 'Product adjustment history retrieved successfully');
    }

    /**
     * Get product transfer history.
     */
    public function transferHistory(Request $request, Product $product): JsonResponse
    {
        if (auth()->user()->denies('view product history')) {
            return response()->forbidden('Permission denied for viewing product history.');
        }

        $filters = $request->only(['warehouse_id', 'start_date', 'end_date']);
        $perPage = $request->integer('per_page', config('app.per_page'));

        $history = $this->service->getTransferHistory($product, $filters, $perPage);

        return response()->success($history, 'Product transfer history retrieved successfully');
    }
}
