<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductPurchase;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\Tax;
use App\Models\Variant;
use App\Models\Warehouse;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Schema;

/**
 * ProductService
 *
 * Handles all business logic related to products.
 *
 * @package App\Services
 */
class ProductService extends BaseService
{
    /**
     * Create a new product with all related data.
     *
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile>|null $images
     * @param UploadedFile|null $file
     * @return Product
     * @throws Exception
     */
    public function createProduct(array $data, ?array $images = null, ?UploadedFile $file = null): Product
    {
        return $this->transaction(function () use ($data, $images, $file) {
            // Prepare product data
            $productData = $this->prepareProductData($data, $images, $file);

            // Create product
            $product = Product::create($productData);

            // Handle custom fields
            $this->handleCustomFields($product, $data);

            // Handle initial stock and auto purchase
            $this->handleInitialStock($product, $data);

            // Handle product variants
            $variantIds = $this->handleProductVariants($product, $data);

            // Handle different pricing per warehouse
            $this->handleDifferentPricing($product, $data, $variantIds);

            // Clear cache
            $this->clearProductCache();

            return $product->fresh(['category', 'brand', 'unit']);
        });
    }

    /**
     * Prepare product data for create/update.
     *
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile>|null $images
     * @param UploadedFile|null $file
     * @param Product|null $existingProduct
     * @return array<string, mixed>
     */
    protected function prepareProductData(
        array         $data,
        ?array        $images = null,
        ?UploadedFile $file = null,
        ?Product      $existingProduct = null
    ): array
    {
        // Handle warranty and guarantee
        if (!isset($data['warranty'])) {
            unset($data['warranty'], $data['warranty_type']);
        }
        if (!isset($data['guarantee'])) {
            unset($data['guarantee'], $data['guarantee_type']);
        }

        // Handle variants
        if (isset($data['is_variant'])) {
            $data['variant_option'] = json_encode(array_unique($data['variant_option'] ?? []));
            $data['variant_value'] = json_encode(array_unique($data['variant_value'] ?? []));
        } else {
            $data['variant_option'] = null;
            $data['variant_value'] = null;
        }

        // Sanitize name
        $data['name'] = preg_replace('/[\n\r]/', '<br>', htmlspecialchars(trim($data['name'] ?? ''), ENT_QUOTES));

        // Generate slug for ecommerce
        if (in_array('ecommerce', explode(',', config('addons', '')))) {
            $data['slug'] = Str::slug($data['name'], '-');
            $data['slug'] = preg_replace('/[^A-Za-z0-9\-]/', '', $data['slug']);
            $data['slug'] = str_replace('\/', '/', $data['slug']);
        }

        // Handle menu type for restaurant
        if (in_array('restaurant', explode(',', config('addons', ''))) && isset($data['menu_type'])) {
            $data['menu_type'] = implode(',', (array)$data['menu_type']);
        }

        // Handle combo/recipe products
        if (($data['type'] ?? '') === 'combo' || (isset($data['is_recipe']) && $data['is_recipe'] == 1)) {
            $data['product_list'] = implode(',', $data['product_id'] ?? []);
            $data['variant_list'] = implode(',', $data['variant_id'] ?? []);
            $data['qty_list'] = implode(',', $data['product_qty'] ?? []);
            $data['price_list'] = implode(',', $data['unit_price'] ?? []);
            $data['wastage_percent'] = implode(',', $data['wastage_percent'] ?? []);
            $data['combo_unit_id'] = implode(',', $data['combo_unit_id'] ?? []);
        }

        // Handle digital/service products
        if (in_array($data['type'] ?? '', ['digital', 'service'])) {
            $data['cost'] = 0;
            $data['unit_id'] = 0;
            $data['purchase_unit_id'] = 0;
            $data['sale_unit_id'] = 0;
        }

        // Sanitize product details
        $data['product_details'] = str_replace('"', '@', $data['product_details'] ?? '');

        // Format dates
        if (isset($data['starting_date'])) {
            $data['starting_date'] = date('Y-m-d', strtotime($data['starting_date']));
        }
        if (isset($data['last_date'])) {
            $data['last_date'] = date('Y-m-d', strtotime($data['last_date']));
        }

        $data['is_active'] = true;

        // Handle images
        $data['image'] = $this->handleProductImages($images, $existingProduct);

        // Handle file
        if ($file) {
            $data['file'] = $this->handleProductFile($file);
        } elseif ($existingProduct) {
            $data['file'] = $existingProduct->file;
        }

        // Handle sync disable
        if (!isset($data['is_sync_disable']) && Schema::hasColumn('products', 'is_sync_disable')) {
            $data['is_sync_disable'] = null;
        }

        return $data;
    }

    /**
     * Handle product images upload and processing.
     *
     * @param array<int, UploadedFile>|null $images
     * @param Product|null $existingProduct
     * @return string
     */
    protected function handleProductImages(?array $images, ?Product $existingProduct = null): string
    {
        if (empty($images)) {
            return $existingProduct?->image ?? 'zummXD2dvAtI.png';
        }

        $this->ensureImageDirectoriesExist();
        $imageNames = [];
        $manager = new ImageManager(new GdDriver());

        foreach ($images as $key => $image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date('Ymdhis') . ($key + 1);

            // Handle multi-tenant logic
            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            } else {
                $tenantId = $this->getTenantId();
                $imageName = $tenantId . '_' . $imageName . '.' . $ext;
            }

            $image->move(public_path('images/product'), $imageName);

            $imageObj = $manager->read(public_path('images/product/' . $imageName));
            $this->storeDifferentSizedImages($imageObj, $imageName);

            $imageNames[] = $imageName;
        }

        return implode(',', $imageNames);
    }

    /**
     * Ensure image directories exist.
     *
     * @return void
     */
    protected function ensureImageDirectoriesExist(): void
    {
        $directories = [
            'images/product/xlarge',
            'images/product/large',
            'images/product/medium',
            'images/product/small',
        ];

        foreach ($directories as $directory) {
            $path = public_path($directory);
            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Get tenant ID.
     *
     * @return string|null
     */
    protected function getTenantId(): ?string
    {
        // Multi-tenant support - implement based on your tenant system
        return null;
    }

    /**
     * Store different sized images.
     *
     * @param mixed $image
     * @param string $imageName
     * @return void
     */
    protected function storeDifferentSizedImages(mixed $image, string $imageName): void
    {
        $image->resize(1000, 1250)->save(public_path('images/product/xlarge/' . $imageName));
        $image->resize(500, 500)->save(public_path('images/product/large/' . $imageName));
        $image->resize(250, 250)->save(public_path('images/product/medium/' . $imageName));
        $image->resize(100, 100)->save(public_path('images/product/small/' . $imageName));
    }

    /**
     * Handle product file upload.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function handleProductFile(UploadedFile $file): string
    {
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $fileName = strtotime(date('Y-m-d H:i:s')) . '.' . $ext;
        $file->move(public_path('product/files'), $fileName);

        return $fileName;
    }

    /**
     * Handle custom fields for product.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @return void
     */
    protected function handleCustomFields(Product $product, array $data): void
    {
        $customFields = CustomField::where('belongs_to', 'product')
            ->select('name', 'type')
            ->get();

        $customFieldData = [];

        foreach ($customFields as $customField) {
            $fieldName = str_replace(' ', '_', strtolower($customField->name));

            if (isset($data[$fieldName])) {
                if (in_array($customField->type, ['checkbox', 'multi_select'])) {
                    $customFieldData[$fieldName] = implode(',', (array)$data[$fieldName]);
                } else {
                    $customFieldData[$fieldName] = $data[$fieldName];
                }
            }
        }

        if (count($customFieldData) > 0) {
            DB::table('products')
                ->where('id', $product->id)
                ->update($customFieldData);
        }
    }

    /**
     * Handle initial stock and auto purchase.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @return void
     */
    protected function handleInitialStock(Product $product, array $data): void
    {
        if (!isset($data['is_initial_stock']) || isset($data['is_variant']) || isset($data['is_batch'])) {
            return;
        }

        $initialStock = 0;

        foreach ($data['stock_warehouse_id'] ?? [] as $key => $warehouseId) {
            $stock = (float)($data['stock'][$key] ?? 0);

            if ($stock > 0) {
                $this->createAutoPurchase($product, (int)$warehouseId, $stock);
                $initialStock += $stock;
            }
        }

        if ($initialStock > 0) {
            $product->qty += $initialStock;
            $product->save();
        }
    }

    /**
     * Create auto purchase for initial stock.
     *
     * @param Product $product
     * @param int $warehouseId
     * @param float $stock
     * @return void
     */
    protected function createAutoPurchase(Product $product, int $warehouseId, float $stock): void
    {
        $referenceNo = 'pr-' . date('Ymd') . '-' . date('his');
        $userId = Auth::id();

        // Calculate tax
        $taxData = $this->calculateProductTax($product, $stock);
        $netUnitCost = $taxData['net_unit_cost'];
        $tax = $taxData['tax'];
        $taxRate = $taxData['tax_rate'];
        $totalCost = $taxData['total_cost'];

        // Update product warehouse
        $productWarehouse = ProductWarehouse::firstOrNew([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
        ]);

        $productWarehouse->qty += $stock;
        $productWarehouse->save();

        // Create purchase
        $purchase = Purchase::create([
            'reference_no' => $referenceNo,
            'user_id' => $userId,
            'warehouse_id' => $warehouseId,
            'item' => 1,
            'total_qty' => $stock,
            'total_discount' => 0,
            'total_tax' => $tax,
            'total_cost' => $totalCost,
            'order_tax' => 0,
            'grand_total' => $totalCost,
            'paid_amount' => $totalCost,
            'status' => 1,
            'payment_status' => 2,
        ]);

        // Create product purchase
        ProductPurchase::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'qty' => $stock,
            'recieved' => $stock,
            'purchase_unit_id' => $product->unit_id,
            'net_unit_cost' => $netUnitCost,
            'discount' => 0,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'total' => $totalCost,
        ]);

        // Create payment
        Payment::create([
            'payment_reference' => 'ppr-' . date('Ymd') . '-' . date('his'),
            'user_id' => $userId,
            'purchase_id' => $purchase->id,
            'account_id' => 0,
            'amount' => $totalCost,
            'change' => 0,
            'paying_method' => 'Cash',
        ]);
    }

    /**
     * Calculate product tax.
     *
     * @param Product $product
     * @param float $qty
     * @return array<string, float>
     */
    protected function calculateProductTax(Product $product, float $qty): array
    {
        if (!$product->tax_id) {
            return [
                'net_unit_cost' => (float)number_format($product->cost, 2, '.', ''),
                'tax' => 0.00,
                'tax_rate' => 0.00,
                'total_cost' => (float)number_format($product->cost * $qty, 2, '.', ''),
            ];
        }

        $tax = Tax::find($product->tax_id);
        $taxRate = (float)$tax->rate;

        if ($product->tax_method == 1) {
            // Tax included
            $netUnitCost = (float)number_format($product->cost, 2, '.', '');
            $taxAmount = (float)number_format($product->cost * $qty * ($taxRate / 100), 2, '.', '');
            $totalCost = (float)number_format(($product->cost * $qty) + $taxAmount, 2, '.', '');
        } else {
            // Tax excluded
            $netUnitCost = (float)number_format((100 / (100 + $taxRate)) * $product->cost, 2, '.', '');
            $taxAmount = (float)number_format(($product->cost - $netUnitCost) * $qty, 2, '.', '');
            $totalCost = (float)number_format($product->cost * $qty, 2, '.', '');
        }

        return [
            'net_unit_cost' => $netUnitCost,
            'tax' => $taxAmount,
            'tax_rate' => $taxRate,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Handle product variants.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @return array<int>
     */
    protected function handleProductVariants(Product $product, array $data): array
    {
        $variantIds = [];

        if (!isset($data['is_variant'])) {
            return $variantIds;
        }

        if (!isset($data['is_batch'])) {
            $data['is_batch'] = null;
        }

        $variantNames = $data['variant_name'] ?? [];

        foreach ($variantNames as $key => $variantName) {
            $variant = Variant::firstOrCreate(['name' => $variantName]);
            $variantIds[] = $variant->id;

            $productVariant = ProductVariant::firstOrNew([
                'product_id' => $product->id,
                'variant_id' => $variant->id,
            ]);

            $productVariant->item_code = $data['item_code'][$key] ?? null;
            $productVariant->additional_cost = (float)($data['additional_cost'][$key] ?? 0);
            $productVariant->additional_price = (float)($data['additional_price'][$key] ?? 0);
            $productVariant->qty = 0;
            $productVariant->position = $key + 1;
            $productVariant->save();
        }

        return $variantIds;
    }

    /**
     * Handle different pricing per warehouse.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @param array<int> $variantIds
     * @return void
     */
    protected function handleDifferentPricing(Product $product, array $data, array $variantIds): void
    {
        if (isset($data['is_diffPrice'])) {
            $diffPrices = $data['diff_price'] ?? [];
            $warehouseIds = $data['warehouse_id'] ?? [];

            foreach ($diffPrices as $key => $diffPrice) {
                if ($diffPrice > 0) {
                    ProductWarehouse::firstOrCreate([
                        'product_id' => $product->id,
                        'warehouse_id' => (int)$warehouseIds[$key],
                        'qty' => 0,
                        'price' => (float)$diffPrice,
                    ]);
                }
            }
        } elseif (!isset($data['is_initial_stock']) && !isset($data['is_batch']) && config('without_stock') == 'yes') {
            $warehouseIds = Warehouse::where('is_active', true)->pluck('id');

            foreach ($warehouseIds as $warehouseId) {
                if (count($variantIds) > 0) {
                    foreach ($variantIds as $variantId) {
                        ProductWarehouse::firstOrCreate([
                            'product_id' => $product->id,
                            'variant_id' => $variantId,
                            'warehouse_id' => $warehouseId,
                            'qty' => 0,
                        ]);
                    }
                } else {
                    ProductWarehouse::firstOrCreate([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'qty' => 0,
                    ]);
                }
            }
        }
    }

    /**
     * Clear product cache.
     *
     * @return void
     */
    protected function clearProductCache(): void
    {
        Cache::forget('product_list');
        Cache::forget('product_list_with_variant');
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile>|null $images
     * @param UploadedFile|null $file
     * @return Product
     * @throws Exception
     */
    public function updateProduct(
        Product       $product,
        array         $data,
        ?array        $images = null,
        ?UploadedFile $file = null
    ): Product
    {
        return $this->transaction(function () use ($product, $data, $images, $file) {
            // Prepare product data
            $productData = $this->prepareProductData($data, $images, $file, $product);

            // Update product
            $product->update($productData);

            // Handle custom fields
            $this->handleCustomFields($product, $data);

            // Handle product variants
            $variantIds = $this->handleProductVariants($product, $data);

            // Handle different pricing per warehouse
            $this->handleDifferentPricing($product, $data, $variantIds);

            // Clear cache
            $this->clearProductCache();

            return $product->fresh(['category', 'brand', 'unit']);
        });
    }

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return bool
     * @throws Exception
     */
    public function deleteProduct(Product $product): bool
    {
        return $this->transaction(function () use ($product) {
            // Check if product can be deleted (no sales/purchases)
            if (!$this->canDeleteProduct($product)) {
                throw new Exception('Product cannot be deleted as it has associated sales or purchases.');
            }

            // Delete related data
            ProductVariant::where('product_id', $product->id)->delete();
            ProductWarehouse::where('product_id', $product->id)->delete();
            ProductBatch::where('product_id', $product->id)->delete();

            // Delete product
            $product->delete();

            // Clear cache
            $this->clearProductCache();

            return true;
        });
    }

    /**
     * Check if product can be deleted.
     *
     * @param Product $product
     * @return bool
     */
    protected function canDeleteProduct(Product $product): bool
    {
        // Check if product has sales
        $hasSales = DB::table('product_sales')
            ->where('product_id', $product->id)
            ->exists();

        // Check if product has purchases
        $hasPurchases = DB::table('product_purchases')
            ->where('product_id', $product->id)
            ->exists();

        return !$hasSales && !$hasPurchases;
    }

    /**
     * Get products with filters and pagination (DataTables support).
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getProducts(array $filters = []): array
    {
        $columns = [
            1 => 'name',
            2 => 'code',
            3 => 'brand_id',
            4 => 'category_id',
            5 => 'qty',
            6 => 'unit_id',
            7 => 'price',
            8 => 'cost',
        ];

        $warehouseId = (int)($filters['warehouse_id'] ?? 0);
        $productType = $filters['product_type'] ?? 'all';
        $brandId = (int)($filters['brand_id'] ?? 0);
        $categoryId = (int)($filters['category_id'] ?? 0);
        $unitId = (int)($filters['unit_id'] ?? 0);
        $taxId = (int)($filters['tax_id'] ?? 0);
        $imeiOrVariant = $filters['imeiorvariant'] ?? '';
        $stockFilter = $filters['stock_filter'] ?? 'all';
        $isRecipe = (bool)($filters['is_recipe'] ?? false);
        $search = $filters['search']['value'] ?? '';

        // Build base query
        $baseQuery = $this->buildProductQuery($stockFilter, $isRecipe, $productType, $brandId, $categoryId, $unitId, $taxId, $imeiOrVariant);

        $totalData = $baseQuery->count();
        $totalFiltered = $totalData;

        // Apply search
        if (!empty($search)) {
            $baseQuery = $this->applyProductSearch($baseQuery, $search);
            $totalFiltered = $baseQuery->distinct()->count('products.id');
        }

        // Apply pagination and ordering
        $limit = ($filters['length'] ?? -1) != -1 ? (int)$filters['length'] : null;
        $start = (int)($filters['start'] ?? 0);
        $orderColumn = $columns[(int)($filters['order'][0]['column'] ?? 1)] ?? 'name';
        $orderDir = $filters['order'][0]['dir'] ?? 'asc';

        if ($limit) {
            $baseQuery->offset($start)->limit($limit);
        }
        $baseQuery->orderBy('products.' . $orderColumn, $orderDir);

        $products = $baseQuery->get();

        // Format data
        $data = $this->formatProductData($products, $warehouseId, $filters);

        return [
            'draw' => (int)($filters['draw'] ?? 1),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];
    }

    /**
     * Build base product query with filters.
     *
     * @param string $stockFilter
     * @param bool $isRecipe
     * @param string $productType
     * @param int $brandId
     * @param int $categoryId
     * @param int $unitId
     * @param int $taxId
     * @param string $imeiOrVariant
     * @return Builder
     */
    protected function buildProductQuery(
        string $stockFilter,
        bool   $isRecipe,
        string $productType,
        int    $brandId,
        int    $categoryId,
        int    $unitId,
        int    $taxId,
        string $imeiOrVariant
    ): Builder
    {
        $query = Product::with(['category', 'brand', 'unit'])
            ->where('products.is_active', true);

        // Stock filter
        if ($stockFilter === 'with') {
            $query->whereIn('products.id', function ($q) {
                $q->select('product_id')
                    ->from('product_warehouse')
                    ->groupBy('product_id')
                    ->havingRaw('SUM(qty) > 0');
            });
        } elseif ($stockFilter === 'without') {
            $query->whereNotIn('products.id', function ($q) {
                $q->select('product_id')
                    ->from('product_warehouse')
                    ->groupBy('product_id')
                    ->havingRaw('SUM(qty) > 0');
            });
        }

        // Recipe filter
        if ($isRecipe) {
            $query->where('is_recipe', true);
        }

        // Product type filter
        if ($productType !== 'all') {
            $query->where('type', $productType);
        }

        // Brand filter
        if ($brandId > 0) {
            $query->where('brand_id', $brandId);
        }

        // Category filter
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        // Unit filter
        if ($unitId > 0) {
            $query->where('unit_id', $unitId);
        }

        // Tax filter
        if ($taxId > 0) {
            $query->where('tax_id', $taxId);
        }

        // IMEI/Variant filter
        if ($imeiOrVariant === 'imei') {
            $query->where('is_imei', true);
        } elseif ($imeiOrVariant === 'variant') {
            $query->where('is_variant', true);
        }

        return $query;
    }

    /**
     * Apply search to product query.
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    protected function applyProductSearch(Builder $query, string $search): Builder
    {
        $productIds = Product::where('name', 'LIKE', "%{$search}%")
            ->orWhere('code', 'LIKE', "%{$search}%")
            ->pluck('id');

        $variantIds = ProductVariant::where('item_code', 'LIKE', "%{$search}%")
            ->pluck('product_id');

        $imeiIds = ProductPurchase::where('imei_number', 'LIKE', "%{$search}%")
            ->pluck('product_id');

        $brandIds = Brand::where('name', 'LIKE', "%{$search}%")
            ->pluck('id');

        $categoryIds = Category::where('name', 'LIKE', "%{$search}%")
            ->pluck('id');

        $customFields = CustomField::where([
            ['belongs_to', 'product'],
            ['is_table', true],
        ])->pluck('name');

        $fieldNames = [];
        foreach ($customFields as $fieldName) {
            $fieldNames[] = str_replace(' ', '_', strtolower($fieldName));
        }

        return $query->where(function ($q) use ($productIds, $variantIds, $imeiIds, $brandIds, $categoryIds, $fieldNames, $search) {
            if ($productIds->isNotEmpty()) {
                $q->whereIn('products.id', $productIds);
            }

            if ($variantIds->isNotEmpty()) {
                $q->orWhereIn('products.id', $variantIds);
            }

            if ($imeiIds->isNotEmpty()) {
                $q->orWhereIn('products.id', $imeiIds);
            }

            if ($brandIds->isNotEmpty()) {
                $q->orWhereIn('products.brand_id', $brandIds);
            }

            if ($categoryIds->isNotEmpty()) {
                $q->orWhereIn('products.category_id', $categoryIds);
            }

            // Custom fields
            foreach ($fieldNames as $fieldName) {
                $safeField = str_replace('`', '', $fieldName);
                $q->orWhere("products.{$safeField}", 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Format product data for response.
     *
     * @param Collection<int, Product> $products
     * @param int $warehouseId
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    protected function formatProductData(Collection $products, int $warehouseId, array $filters): array
    {
        $data = [];
        $customFields = CustomField::where([
            ['belongs_to', 'product'],
            ['is_table', true],
        ])->pluck('name');

        $fieldNames = [];
        foreach ($customFields as $fieldName) {
            $fieldNames[] = str_replace(' ', '_', strtolower($fieldName));
        }

        foreach ($products as $key => $product) {
            $nestedData = [
                'id' => $product->id,
                'key' => $key,
                'name' => $product->name,
                'code' => $product->code,
                'brand' => $product->brand->name ?? 'N/A',
                'category' => $product->category->name ?? 'N/A',
                'unit' => $product->unit->unit_name ?? 'N/A',
                'price' => $product->price,
                'cost' => $product->cost,
            ];

            // Calculate quantity
            if ($warehouseId > 0 && $product->type === 'standard') {
                $nestedData['qty'] = ProductWarehouse::where([
                    ['product_id', $product->id],
                    ['warehouse_id', $warehouseId],
                ])->sum('qty');
            } elseif ($product->type === 'standard') {
                $nestedData['qty'] = ProductWarehouse::where('product_id', $product->id)->sum('qty');
            } else {
                $nestedData['qty'] = $product->qty;
            }

            // Custom fields
            foreach ($fieldNames as $fieldName) {
                $nestedData[$fieldName] = $product->$fieldName ?? null;
            }

            $data[] = $nestedData;
        }

        return $data;
    }

    /**
     * Get product by ID with relationships.
     *
     * @param int $id
     * @return Product|null
     */
    public function getProduct(int $id): ?Product
    {
        return Product::with(['category', 'brand', 'unit', 'tax', 'productVariants.variant', 'productWarehouses.warehouse'])
            ->find($id);
    }

    /**
     * Get product stock levels.
     *
     * @param int $productId
     * @param int|null $warehouseId
     * @return array<string, mixed>
     */
    public function getProductStock(int $productId, ?int $warehouseId = null): array
    {
        $product = Product::findOrFail($productId);

        if ($product->type === 'standard') {
            $query = ProductWarehouse::where('product_id', $productId);

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $stock = $query->sum('qty');
        } else {
            $stock = $product->qty;
        }

        return [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'stock' => (float)$stock,
            'alert_quantity' => $product->alert_quantity,
            'is_low_stock' => $stock <= $product->alert_quantity,
        ];
    }

    /**
     * Get products without variants.
     *
     * @return Collection<int, Product>
     */
    public function getProductsWithoutVariant(): Collection
    {
        return Cache::remember('product_list', 3600, function () {
            return Product::where('is_active', true)
                ->where('is_variant', false)
                ->where('type', '!=', 'combo')
                ->select('id', 'name', 'code', 'qty', 'price')
                ->get();
        });
    }

    /**
     * Get products with variants.
     *
     * @return Collection<int, Product>
     */
    public function getProductsWithVariant(): Collection
    {
        return Cache::remember('product_list_with_variant', 3600, function () {
            return Product::where('is_active', true)
                ->where('is_variant', true)
                ->with('productVariants.variant')
                ->select('id', 'name', 'code')
                ->get();
        });
    }
}

