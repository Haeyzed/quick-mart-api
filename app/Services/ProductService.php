<?php

declare(strict_types=1);

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\Adjustment;
use App\Models\CustomField;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductPurchase;
use App\Models\ProductSale;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\PurchaseProductReturn;
use App\Models\ReturnPurchase;
use App\Models\Returns;
use App\Models\Sale;
use App\Models\Transfer;
use App\Models\Unit;
use App\Models\Variant;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class ProductService
 *
 * Handles all core business logic and database interactions for Products.
 */
class ProductService extends BaseService
{
    private const IMAGE_PATH = 'images/product';

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated products.
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category:id,name', 'brand:id,name', 'unit:id,name,unit_code'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a specific product with its relationships.
     */
    public function get(Product $product): Product
    {
        return $product->load([
            'category', 'brand', 'unit', 'purchaseUnit', 'saleUnit', 'tax',
            'productVariants.variant', 'productWarehouses.warehouse'
        ]);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return $this->transaction(function () use ($data) {
            $data = $this->prepareProductData($data);
            $data = $this->handleImages($data);

            $product = Product::query()->create($data);

            $this->handleVariants($product, $data['variants'] ?? []);
            $this->handleWarehousePrices($product, $data['warehouse_prices'] ?? []);
            $this->handleInitialStock($product, $data);
            $this->handleCustomFields($product, $data);

            $this->clearCache(['product_list', 'product_list_with_variant']);

            return $product->fresh(['category', 'brand', 'unit']);
        });
    }

    /**
     * Update an existing product.
     */
    public function update(Product $product, array $data): Product
    {
        return $this->transaction(function () use ($product, $data) {
            $data = $this->prepareProductData($data);
            $data = $this->handleImages($data, $product);

            $product->update($data);

            $this->handleVariants($product, $data['variants'] ?? []);
            $this->handleWarehousePrices($product, $data['warehouse_prices'] ?? []);
            $this->handleCustomFields($product, $data);

            $this->clearCache(['product_list', 'product_list_with_variant']);

            return $product->fresh(['category', 'brand', 'unit']);
        });
    }

    /**
     * Delete a product safely.
     */
    public function delete(Product $product): void
    {
        $this->transaction(function () use ($product) {
            // Check constraints (e.g., if it has sales/purchases)
            if ($product->sales()->exists() || $product->purchases()->exists()) {
                throw new ConflictHttpException("Cannot delete product '{$product->name}' as it is linked to existing transactions.");
            }

            // Cleanup images
            if (!empty($product->image_path)) {
                foreach ($product->image_path as $image) {
                    $this->uploadService->delete($image);
                }
            }
            if ($product->file_url) {
                $this->uploadService->delete($product->file_url);
            }

            // Delete relations
            $product->productVariants()->delete();
            $product->productWarehouses()->delete();

            $product->delete();

            $this->clearCache(['product_list', 'product_list_with_variant']);
        });
    }

    /**
     * Bulk delete products safely.
     */
    public function bulkDelete(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            $products = Product::query()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($products as $product) {
                try {
                    $this->delete($product);
                    $count++;
                } catch (ConflictHttpException $e) {
                    continue; // Skip products that can't be deleted
                }
            }

            return $count;
        });
    }

    /**
     * Import multiple products from an uploaded file.
     */
    public function import(UploadedFile $file): void
    {
        ExcelFacade::import(new ProductsImport, $file);
        $this->clearCache(['product_list', 'product_list_with_variant']);
    }

    /**
     * Prepare specific product data fields before saving.
     */
    private function prepareProductData(array $data): array
    {
        if (isset($data['type']) && $data['type'] === 'combo') {
            if (isset($data['combo_products'])) {
                $data['product_list'] = implode(',', array_column($data['combo_products'], 'product_id'));
                $data['variant_list'] = implode(',', array_column($data['combo_products'], 'variant_id'));
                $data['qty_list'] = implode(',', array_column($data['combo_products'], 'qty'));
                $data['price_list'] = implode(',', array_column($data['combo_products'], 'price'));
                $data['wastage_percent'] = implode(',', array_column($data['combo_products'], 'wastage_percent'));
                $data['combo_unit_id'] = implode(',', array_column($data['combo_products'], 'combo_unit_id'));
            }
        } elseif (isset($data['type']) && in_array($data['type'], ['digital', 'service'])) {
            $data['cost'] = 0;
            $data['unit_id'] = null;
            $data['purchase_unit_id'] = null;
            $data['sale_unit_id'] = null;
        }

        // Calculate profit margin if not set but cost and price are set
        if (!isset($data['profit_margin']) && isset($data['cost'], $data['price']) && $data['cost'] > 0) {
            $data['profit_margin'] = round((((float)$data['price'] - (float)$data['cost']) / (float)$data['cost']) * 100, 2);
        }

        // Ensure unique variant options and values
        if (isset($data['is_variant']) && $data['is_variant']) {
            if (isset($data['variant_option']) && is_array($data['variant_option'])) {
                $data['variant_option'] = array_values(array_unique($data['variant_option']));
            }
            if (isset($data['variant_value']) && is_array($data['variant_value'])) {
                $data['variant_value'] = array_values(array_unique($data['variant_value']));
            }
        } else {
            $data['variant_option'] = null;
            $data['variant_value'] = null;
        }

        // Handle stringification of product_details as requested in old system (optional, as JSON is better)
        if (isset($data['product_details']) && is_string($data['product_details'])) {
            $data['product_details'] = str_replace('"', '@', $data['product_details']);
        }

        return $data;
    }

    /**
     * Handle Image Uploads for Products (supports multiple images).
     */
    private function handleImages(array $data, ?Product $product = null): array
    {
        $existingImages = $product ? ($product->image_path ?? []) : [];
        $existingUrls = $product ? ($product->image_url ?? []) : [];

        // Handle deletions
        if (isset($data['deleted_images'])) {
            foreach ($data['deleted_images'] as $imgToDelete) {
                $this->uploadService->delete($imgToDelete);
                $key = array_search($imgToDelete, $existingImages);
                if ($key !== false) {
                    unset($existingImages[$key]);
                    unset($existingUrls[$key]);
                }
            }
            $existingImages = array_values($existingImages);
            $existingUrls = array_values($existingUrls);
        }

        // Handle new uploads
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                if ($image instanceof UploadedFile) {
                    $path = $this->uploadService->upload($image, self::IMAGE_PATH);
                    $existingImages[] = $path;
                    $existingUrls[] = $this->uploadService->url($path);
                }
            }
        }

        $data['image_path'] = $existingImages;
        $data['image_url'] = $existingUrls;

        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            if ($product?->file_url) {
                $this->uploadService->delete($product->file_url);
            }
            $path = $this->uploadService->upload($data['file'], 'product/files');
            $data['file_url'] = $path;
        }

        return $data;
    }

    /**
     * Handle Product Variants insertion and syncing.
     */
    private function handleVariants(Product $product, array $variantsData): void
    {
        if (!$product->is_variant || empty($variantsData)) {
            $product->productVariants()->delete();
            return;
        }

        $currentVariantIds = [];
        $warehouseIds = Warehouse::active()->pluck('id');

        foreach ($variantsData as $index => $vData) {
            $variant = Variant::firstOrCreate(['name' => $vData['name']]);

            $productVariant = ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'variant_id' => $variant->id],
                [
                    'position' => $index + 1,
                    'item_code' => $vData['item_code'] ?? $variant->name . '-' . $product->code,
                    'additional_cost' => $vData['additional_cost'] ?? 0,
                    'additional_price' => $vData['additional_price'] ?? 0,
                ]
            );

            $currentVariantIds[] = $productVariant->id;

            // Ensure warehouse pivot exists for this variant
            foreach ($warehouseIds as $wId) {
                ProductWarehouse::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'warehouse_id' => $wId,
                ], ['qty' => 0]);
            }
        }

        // Remove deleted variants
        $variantsToDelete = ProductVariant::where('product_id', $product->id)
            ->whereNotIn('id', $currentVariantIds)
            ->get();

        foreach ($variantsToDelete as $vDel) {
            if ($vDel->qty > 0) {
                throw new ConflictHttpException("Cannot delete variant '{$vDel->variant->name}' because it has stock.");
            }
            ProductWarehouse::where('product_id', $product->id)
                ->where('variant_id', $vDel->variant_id)
                ->delete();
            $vDel->delete();
        }
    }

    /**
     * Handle specific pricing per warehouse.
     */
    private function handleWarehousePrices(Product $product, array $warehousePrices): void
    {
        if (!$product->is_diff_price || empty($warehousePrices)) {
            ProductWarehouse::where('product_id', $product->id)->update(['price' => null]);
            return;
        }

        foreach ($warehousePrices as $wpData) {
            ProductWarehouse::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $wpData['warehouse_id'],
                    'variant_id' => null // Assuming diff price is usually base product level
                ],
                ['price' => $wpData['price']]
            );
        }
    }

    /**
     * Handle initial stock allocation during product creation.
     */
    private function handleInitialStock(Product $product, array $data): void
    {
        if (empty($data['is_initial_stock']) || empty($data['initial_stock']) || $product->is_variant || $product->is_batch) {
            // Default empty stock if config requires it
            if (config('app.without_stock') === 'yes') {
                 $warehouseIds = Warehouse::active()->pluck('id');
                 foreach($warehouseIds as $wId) {
                     ProductWarehouse::firstOrCreate([
                         'product_id' => $product->id,
                         'warehouse_id' => $wId,
                     ], ['qty' => 0]);
                 }
            }
            return;
        }

        $totalInitialStock = 0;
        foreach ($data['initial_stock'] as $stockData) {
            $stock = (float) $stockData['qty'];
            if ($stock > 0) {
                $warehouseId = (int) $stockData['warehouse_id'];
                $this->createAutoPurchase($product, $warehouseId, $stock);
                $totalInitialStock += $stock;
            }
        }

        if ($totalInitialStock > 0) {
            $product->increment('qty', $totalInitialStock);
        }
    }

    /**
     * Process auto purchase for initial stock.
     */
    private function createAutoPurchase(Product $product, int $warehouseId, float $stock): void
    {
        $tax = 0.00;
        $cost = $product->cost * $stock;
        $netUnitCost = $product->cost;
        $taxRate = 0.00;

        if ($product->tax_id) {
            $taxData = DB::table('taxes')->find($product->tax_id);
            if ($taxData) {
                if ($product->tax_method == 1) { // Exclusive
                    $tax = ($product->cost * $stock) * ($taxData->rate / 100);
                    $cost = ($product->cost * $stock) + $tax;
                    $netUnitCost = $product->cost;
                } else { // Inclusive
                    $netUnitCost = (100 / (100 + $taxData->rate)) * $product->cost;
                    $tax = ($product->cost - $netUnitCost) * $stock;
                    $cost = $product->cost * $stock;
                }
                $taxRate = (float) $taxData->rate;
            }
        }

        $purchase = Purchase::create([
            'reference_no' => 'pr-' . date('Ymd') . '-' . date('His') . rand(10, 99),
            'user_id' => auth()->id() ?? 1,
            'warehouse_id' => $warehouseId,
            'item' => 1,
            'total_qty' => $stock,
            'total_discount' => 0,
            'total_tax' => $tax,
            'total_cost' => $cost,
            'order_tax_rate' => 0,
            'order_tax' => 0,
            'order_discount' => 0,
            'shipping_cost' => 0,
            'grand_total' => $cost,
            'paid_amount' => $cost,
            'status' => 1, // 1 = Received
            'payment_status' => 2, // 2 = Paid
        ]);

        ProductPurchase::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'qty' => $stock,
            'recieved' => $stock,
            'purchase_unit_id' => $product->unit_id ?? 1,
            'net_unit_cost' => $netUnitCost,
            'discount' => 0,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'total' => $cost,
        ]);

        Payment::create([
            'payment_reference' => 'ppr-' . date('Ymd') . '-' . date('His') . rand(10, 99),
            'user_id' => auth()->id() ?? 1,
            'purchase_id' => $purchase->id,
            'account_id' => 1, // Default account
            'amount' => $cost,
            'change' => 0,
            'paying_method' => 'Cash',
        ]);

        $productWarehouse = ProductWarehouse::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
        ], ['qty' => 0]);

        $productWarehouse->increment('qty', $stock);
    }

    /**
     * Handle dynamic custom fields for products.
     */
    private function handleCustomFields(Product $product, array $data): void
    {
        $customFields = CustomField::where('belongs_to', 'product')->get();
        $updateData = [];

        foreach ($customFields as $field) {
            $fieldName = str_replace(' ', '_', strtolower($field->name));
            if (isset($data[$fieldName])) {
                $updateData[$fieldName] = is_array($data[$fieldName])
                    ? implode(',', $data[$fieldName])
                    : $data[$fieldName];
            }
        }

        if (!empty($updateData)) {
            // As custom fields are usually added as columns to the table in this architecture
            DB::table('products')->where('id', $product->id)->update($updateData);
        }
    }

    /**
     * Generate unique product code.
     */
    public function generateCode(): string
    {
        do {
            $code = (string) mt_rand(10000000, 99999999);
        } while (Product::where('code', $code)->exists());

        return $code;
    }

    /**
     * Search products by keyword (name, code, variant item_code).
     */
    public function search(string $keyword, ?int $warehouseId = null): Collection
    {
        return Product::with([
            'brand', 'category', 'unit', 'tax',
            'productVariants',
            'productWarehouses' => function($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            }
        ])
        ->where('is_active', true)
        ->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('code', 'like', "%{$keyword}%")
              ->orWhereHas('productVariants', function ($subQ) use ($keyword) {
                  $subQ->where('item_code', 'like', "%{$keyword}%");
              });
        })->get();
    }

    /**
     * Get sale units based on base unit ID.
     */
    public function getSaleUnits(int $unitId): Collection
    {
        return Unit::where('base_unit', $unitId)
            ->where('is_active', true)
            ->orWhere('id', $unitId)
            ->get(['id', 'unit_name', 'unit_code', 'operator', 'operation_value']);
    }

    /**
     * Reorder multiple images for a product.
     */
    public function reorderImages(Product $product, array $imageUrls): Product
    {
        $product->update([
            'image_path' => $imageUrls, // Assuming the array matches the paths or URLs correctly
        ]);

        return $product->fresh();
    }

    /**
     * Get Products Without Variant.
     */
    public function getProductsWithoutVariant(): Collection
    {
        return Product::activeStandard()
            ->select(['id', 'name', 'code'])
            ->whereNull('is_variant')
            ->get();
    }

    /**
     * Get Products With Variant.
     */
    public function getProductsWithVariant(): Collection
    {
        return Product::join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->activeStandard()
            ->whereNotNull('is_variant')
            ->select('products.id', 'products.name', 'product_variants.item_code', 'product_variants.qty')
            ->orderBy('product_variants.position')
            ->get();
    }

    // --- History Reporting Methods ---

    public function getSaleHistory(Product $product, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $product->sales()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name'])
            ->when(isset($filters['warehouse_id']), fn($q) => $q->where('sales.warehouse_id', $filters['warehouse_id']))
            ->when(isset($filters['start_date']), fn($q) => $q->whereDate('sales.created_at', '>=', $filters['start_date']))
            ->when(isset($filters['end_date']), fn($q) => $q->whereDate('sales.created_at', '<=', $filters['end_date']))
            ->orderBy('sales.created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPurchaseHistory(Product $product, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $product->purchases()
            ->with(['supplier:id,name,phone_number', 'warehouse:id,name'])
            ->when(isset($filters['warehouse_id']), fn($q) => $q->where('purchases.warehouse_id', $filters['warehouse_id']))
            ->when(isset($filters['start_date']), fn($q) => $q->whereDate('purchases.created_at', '>=', $filters['start_date']))
            ->when(isset($filters['end_date']), fn($q) => $q->whereDate('purchases.created_at', '<=', $filters['end_date']))
            ->orderBy('purchases.created_at', 'desc')
            ->paginate($perPage);
    }

    public function getSaleReturnHistory(Product $product, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $product->saleReturns()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name'])
            ->when(isset($filters['warehouse_id']), fn($q) => $q->where('returns.warehouse_id', $filters['warehouse_id']))
            ->when(isset($filters['start_date']), fn($q) => $q->whereDate('returns.created_at', '>=', $filters['start_date']))
            ->when(isset($filters['end_date']), fn($q) => $q->whereDate('returns.created_at', '<=', $filters['end_date']))
            ->orderBy('returns.created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPurchaseReturnHistory(Product $product, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $product->purchaseReturns()
            ->with(['supplier:id,name,phone_number', 'warehouse:id,name'])
            ->when(isset($filters['warehouse_id']), fn($q) => $q->where('return_purchases.warehouse_id', $filters['warehouse_id']))
            ->when(isset($filters['start_date']), fn($q) => $q->whereDate('return_purchases.created_at', '>=', $filters['start_date']))
            ->when(isset($filters['end_date']), fn($q) => $q->whereDate('return_purchases.created_at', '<=', $filters['end_date']))
            ->orderBy('return_purchases.created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAdjustmentHistory(Product $product, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $product->adjustments()
            ->with(['warehouse:id,name'])
            ->when(isset($filters['warehouse_id']), fn($q) => $q->where('adjustments.warehouse_id', $filters['warehouse_id']))
            ->when(isset($filters['start_date']), fn($q) => $q->whereDate('adjustments.created_at', '>=', $filters['start_date']))
            ->when(isset($filters['end_date']), fn($q) => $q->whereDate('adjustments.created_at', '<=', $filters['end_date']))
            ->orderBy('adjustments.created_at', 'desc')
            ->paginate($perPage);
    }

    public function getTransferHistory(Product $product, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $product->transfers()
            ->with(['fromWarehouse:id,name', 'toWarehouse:id,name'])
            ->when(isset($filters['warehouse_id']), fn($q) => $q->where(function($sq) use ($filters) {
                $sq->where('transfers.from_warehouse_id', $filters['warehouse_id'])
                   ->orWhere('transfers.to_warehouse_id', $filters['warehouse_id']);
            }))
            ->when(isset($filters['start_date']), fn($q) => $q->whereDate('transfers.created_at', '>=', $filters['start_date']))
            ->when(isset($filters['end_date']), fn($q) => $q->whereDate('transfers.created_at', '<=', $filters['end_date']))
            ->orderBy('transfers.created_at', 'desc')
            ->paginate($perPage);
    }
}
