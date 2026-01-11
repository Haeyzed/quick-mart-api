<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Unit;
use App\Models\Variant;
use App\Models\Warehouse;
use App\Traits\TenantInfo;
use App\Traits\CacheForget;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Exception;

/**
 * ProductService
 *
 * Handles all business logic for product operations including CRUD operations,
 * filtering, pagination, variants, batches, warehouses, images, and bulk operations.
 */
class ProductService extends BaseService
{
    use CacheForget;
    use TenantInfo;

    public function __construct(
        private readonly UploadService $uploadService
    ) {
    }

    /**
     * Get paginated list of products with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: warehouse_id, product_type, brand_id, category_id, unit_id, tax_id, is_imei, is_variant, stock_filter, is_recipe, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Product>
     */
    public function getProducts(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $warehouseId = $filters['warehouse_id'] ?? 0;
        $stockFilter = $filters['stock_filter'] ?? 'all';
        $isRecipe = $filters['is_recipe'] ?? false;

        // Base query with relations
        $query = Product::with(['category:id,name', 'brand:id,name', 'unit:id,unit_name,unit_code'])
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
            $query->where('is_recipe', 1);
        }

        // Apply filters
        if (isset($filters['product_type']) && $filters['product_type'] !== 'all') {
            $query->where('type', $filters['product_type']);
        }
        if (isset($filters['brand_id']) && $filters['brand_id'] != '0') {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (isset($filters['category_id']) && $filters['category_id'] != '0') {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['unit_id']) && $filters['unit_id'] != '0') {
            $query->where('unit_id', $filters['unit_id']);
        }
        if (isset($filters['tax_id']) && $filters['tax_id'] != '0') {
            $query->where('tax_id', $filters['tax_id']);
        }
        if (isset($filters['is_imei']) && $filters['is_imei'] == '1') {
            $query->where('is_imei', 1);
        }
        if (isset($filters['is_variant']) && $filters['is_variant'] == '1') {
            $query->where('is_variant', 1);
        }

        // Search functionality
        if (!empty($filters['search'] ?? null)) {
            $search = $filters['search'];
            
            $productIds = Product::where('name', 'LIKE', "%{$search}%")
                ->orWhere('code', 'LIKE', "%{$search}%")
                ->pluck('id');
            
            $variantIds = ProductVariant::where('item_code', 'LIKE', "%{$search}%")
                ->pluck('product_id');
            
            $brandIds = Brand::where('name', 'LIKE', "%{$search}%")
                ->pluck('id');
            
            $categoryIds = Category::where('name', 'LIKE', "%{$search}%")
                ->pluck('id');
            
            $query->where(function ($q) use ($productIds, $variantIds, $brandIds, $categoryIds, $search) {
                if ($productIds->isNotEmpty()) {
                    $q->whereIn('products.id', $productIds);
                }
                if ($variantIds->isNotEmpty()) {
                    $q->orWhereIn('products.id', $variantIds);
                }
                if ($brandIds->isNotEmpty()) {
                    $q->orWhereIn('products.brand_id', $brandIds);
                }
                if ($categoryIds->isNotEmpty()) {
                    $q->orWhereIn('products.category_id', $categoryIds);
                }
            });
        }

        return $query->latest('products.id')->paginate($perPage);
    }

    /**
     * Get a single product by ID with all relations.
     *
     * @param int $id Product ID
     * @return Product
     */
    public function getProduct(int $id): Product
    {
        return Product::with([
            'category:id,name',
            'brand:id,name',
            'unit:id,unit_name,unit_code',
            'purchaseUnit:id,unit_name,unit_code',
            'saleUnit:id,unit_name,unit_code',
            'tax:id,name,rate',
            'productVariants.variant:id,name',
            'productWarehouses.warehouse:id,name'
        ])->findOrFail($id);
    }

    /**
     * Create a new product with all related data.
     *
     * @param array<string, mixed> $data Validated product data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        return $this->transaction(function () use ($data) {
            // Normalize data
            $data = $this->normalizeProductData($data, false);

            // Handle images
            if (isset($data['image']) && is_array($data['image'])) {
                $imagePaths = $this->handleImageUploads($data['image']);
                $data['image'] = $imagePaths['paths'];
                $data['image_url'] = $imagePaths['urls'];
            } else {
                $data['image'] = ['zummXD2dvAtI.png'];
                $data['image_url'] = null;
            }

            // Handle file upload (for digital products)
            if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                $filePath = $this->uploadService->upload(
                    $data['file'],
                    'product/files',
                    'public'
                );
                $data['file'] = $filePath;
                $data['file_url'] = $this->uploadService->url($filePath, 'public');
            }

            // Handle combo/recipe products
            if (($data['type'] == 'combo' || (isset($data['is_recipe']) && $data['is_recipe'] == 1)) && isset($data['product_id'])) {
                $data['product_list'] = is_array($data['product_id']) ? implode(",", $data['product_id']) : $data['product_id'];
                $data['variant_list'] = isset($data['variant_id']) && is_array($data['variant_id']) ? implode(",", $data['variant_id']) : ($data['variant_id'] ?? null);
                $data['qty_list'] = isset($data['product_qty']) && is_array($data['product_qty']) ? implode(",", $data['product_qty']) : ($data['product_qty'] ?? null);
                $data['price_list'] = isset($data['unit_price']) && is_array($data['unit_price']) ? implode(",", $data['unit_price']) : ($data['unit_price'] ?? null);
                $data['wastage_percent'] = isset($data['wastage_percent']) && is_array($data['wastage_percent']) ? implode(",", $data['wastage_percent']) : ($data['wastage_percent'] ?? null);
                $data['combo_unit_id'] = isset($data['combo_unit_id']) && is_array($data['combo_unit_id']) ? implode(",", $data['combo_unit_id']) : ($data['combo_unit_id'] ?? null);
            } elseif ($data['type'] == 'digital' || $data['type'] == 'service') {
                $data['cost'] = 0;
                $data['unit_id'] = 0;
                $data['purchase_unit_id'] = 0;
                $data['sale_unit_id'] = 0;
            }

            // Handle variants
            if (isset($data['is_variant']) && $data['is_variant']) {
                if (isset($data['variant_option']) && is_array($data['variant_option'])) {
                    $data['variant_option'] = json_encode(array_unique($data['variant_option']));
                }
                if (isset($data['variant_value']) && is_array($data['variant_value'])) {
                    $data['variant_value'] = json_encode(array_unique($data['variant_value']));
                }
            } else {
                $data['variant_option'] = null;
                $data['variant_value'] = null;
            }

            // Handle slug for ecommerce
            $generalSetting = GeneralSetting::latest()->first();
            if ($generalSetting && in_array('ecommerce', explode(',', $generalSetting->modules ?? ''))) {
                if (!isset($data['slug'])) {
                    $data['slug'] = Str::slug($data['name'], '-');
                }
                $data['slug'] = preg_replace('/[^A-Za-z0-9\-]/', '', $data['slug']);
                $data['slug'] = str_replace('\/', '/', $data['slug']);
            }

            // Handle menu type for restaurant
            if ($generalSetting && in_array('restaurant', explode(',', $generalSetting->modules ?? ''))) {
                if (isset($data['menu_type']) && is_array($data['menu_type'])) {
                    $data['menu_type'] = implode(",", $data['menu_type']);
                }
            }

            // Handle product details
            if (isset($data['product_details'])) {
                $data['product_details'] = str_replace('"', '@', $data['product_details']);
            }

            // Handle dates
            if (isset($data['starting_date'])) {
                $data['starting_date'] = date('Y-m-d', strtotime($data['starting_date']));
            }
            if (isset($data['last_date'])) {
                $data['last_date'] = date('Y-m-d', strtotime($data['last_date']));
            }

            // Default values
            $data['is_active'] = true;
            if (!isset($data['profit_margin'])) {
                $data['profit_margin'] = 0;
            }
            if (!isset($data['is_sync_disable']) && Schema::hasColumn('products', 'is_sync_disable')) {
                $data['is_sync_disable'] = null;
            }

            // Create product
            $product = Product::create($data);

            // Handle custom fields
            $this->handleCustomFields($product, $data);

            // Handle initial stock and auto purchase
            if (isset($data['is_initial_stock']) && !isset($data['is_variant']) && !isset($data['is_batch'])) {
                $initialStock = 0;
                if (isset($data['stock_warehouse_id']) && is_array($data['stock_warehouse_id'])) {
                    foreach ($data['stock_warehouse_id'] as $key => $warehouseId) {
                        $stock = $data['stock'][$key] ?? 0;
                        if ($stock > 0) {
                            $this->autoPurchase($product, $warehouseId, $stock);
                            $initialStock += $stock;
                        }
                    }
                }
                if ($initialStock > 0) {
                    $product->qty += $initialStock;
                    $product->save();
                }
            }

            // Handle product variants
            if (isset($data['is_variant']) && $data['is_variant'] && isset($data['variant_name'])) {
                $this->handleProductVariants($product, $data);
            }

            // Handle different prices per warehouse
            if (isset($data['is_diffPrice']) && isset($data['diff_price']) && is_array($data['diff_price'])) {
                foreach ($data['diff_price'] as $key => $diffPrice) {
                    if ($diffPrice) {
                        ProductWarehouse::firstOrCreate([
                            "product_id" => $product->id,
                            "warehouse_id" => $data["warehouse_id"][$key] ?? null,
                            "qty" => 0,
                            "price" => $diffPrice
                        ]);
                    }
                }
            } elseif (!isset($data['is_initial_stock']) && !isset($data['is_batch'])) {
                // Create warehouse entries for variants if without_stock config is yes
                $warehouseIds = Warehouse::where('is_active', true)->pluck('id');
                if (isset($data['is_variant']) && $data['is_variant']) {
                    $variantIds = ProductVariant::where('product_id', $product->id)->pluck('variant_id');
                    foreach ($warehouseIds as $warehouseId) {
                        foreach ($variantIds as $variantId) {
                            ProductWarehouse::firstOrCreate([
                                "product_id" => $product->id,
                                "variant_id" => $variantId,
                                "warehouse_id" => $warehouseId,
                                "qty" => 0,
                            ]);
                        }
                    }
                } else {
                    foreach ($warehouseIds as $warehouseId) {
                        ProductWarehouse::firstOrCreate([
                            "product_id" => $product->id,
                            "warehouse_id" => $warehouseId,
                            "qty" => 0,
                        ]);
                    }
                }
            }

            // Clear cache
            $this->cacheForget('product_list');
            $this->cacheForget('product_list_with_variant');

            return $product->fresh(['category', 'brand', 'unit']);
        });
    }

    /**
     * Update an existing product.
     *
     * @param Product $product Product instance to update
     * @param array<string, mixed> $data Validated product data
     * @return Product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return $this->transaction(function () use ($product, $data) {
            // Normalize data
            $data = $this->normalizeProductData($data, true);

            // Handle images
            $previousImages = [];
            if (isset($data['prev_img']) && is_array($data['prev_img'])) {
                foreach ($data['prev_img'] as $prevImg) {
                    if (!in_array($prevImg, $previousImages)) {
                        $previousImages[] = $prevImg;
                    }
                }
                $data['image'] = $previousImages;
            } elseif (!isset($data['prev_img'])) {
                $data['image'] = null;
            }

            if (isset($data['image']) && is_array($data['image'])) {
                $newImages = [];
                foreach ($data['image'] as $image) {
                    if ($image instanceof UploadedFile) {
                        $newImages[] = $image;
                    }
                }
                if (!empty($newImages)) {
                    $imagePaths = $this->handleImageUploads($newImages, count($previousImages ?? []));
                    $data['image'] = array_merge($previousImages ?? [], $imagePaths['paths']);
                    $data['image_url'] = $imagePaths['urls'];
                } else {
                    $data['image'] = $previousImages ?? $product->image;
                }
            } else {
                $data['image'] = $previousImages ?? $product->image;
            }

            // Handle file upload
            if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                // Delete old file if exists
                if ($product->file && $this->uploadService->exists($product->file, 'public')) {
                    $this->uploadService->delete($product->file, 'public');
                }
                $filePath = $this->uploadService->upload(
                    $data['file'],
                    'product/files',
                    'public'
                );
                $data['file'] = $filePath;
                $data['file_url'] = $this->uploadService->url($filePath, 'public');
            }

            // Handle combo/recipe products
            if ($data['type'] == 'combo' && isset($data['product_id'])) {
                $data['product_list'] = is_array($data['product_id']) ? implode(",", $data['product_id']) : $data['product_id'];
                $data['variant_list'] = isset($data['variant_id']) && is_array($data['variant_id']) ? implode(",", $data['variant_id']) : ($data['variant_id'] ?? null);
                $data['qty_list'] = isset($data['product_qty']) && is_array($data['product_qty']) ? implode(",", $data['product_qty']) : ($data['product_qty'] ?? null);
                $data['price_list'] = isset($data['unit_price']) && is_array($data['unit_price']) ? implode(",", $data['unit_price']) : ($data['unit_price'] ?? null);
                $data['wastage_percent'] = isset($data['wastage_percent']) && is_array($data['wastage_percent']) ? implode(",", $data['wastage_percent']) : ($data['wastage_percent'] ?? null);
                $data['combo_unit_id'] = isset($data['combo_unit_id']) && is_array($data['combo_unit_id']) ? implode(",", $data['combo_unit_id']) : ($data['combo_unit_id'] ?? null);
            } elseif ($data['type'] == 'digital' || $data['type'] == 'service') {
                $data['cost'] = 0;
                $data['unit_id'] = 0;
                $data['purchase_unit_id'] = 0;
                $data['sale_unit_id'] = 0;
            }

            // Handle variants
            $oldProductVariantIds = ProductVariant::where('product_id', $product->id)->pluck('id')->toArray();
            $newProductVariantIds = [];
            
            if (isset($data['is_variant']) && $data['is_variant']) {
                if (isset($data['variant_option']) && is_array($data['variant_option']) && isset($data['variant_value']) && is_array($data['variant_value'])) {
                    $data['variant_option'] = json_encode(array_unique($data['variant_option']));
                    $data['variant_value'] = json_encode(array_unique($data['variant_value']));
                }
                
                if (isset($data['variant_name']) && is_array($data['variant_name'])) {
                    foreach ($data['variant_name'] as $key => $variantName) {
                        $variant = Variant::firstOrCreate(['name' => $variantName]);
                        $productVariant = ProductVariant::where([
                            ['product_id', $product->id],
                            ['variant_id', $variant->id]
                        ])->first();
                        
                        if ($productVariant) {
                            $productVariant->update([
                                'position' => $key + 1,
                                'item_code' => $data['item_code'][$key] ?? null,
                                'additional_cost' => $data['additional_cost'][$key] ?? 0,
                                'additional_price' => $data['additional_price'][$key] ?? 0
                            ]);
                        } else {
                            $productVariant = ProductVariant::create([
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'item_code' => $data['item_code'][$key] ?? null,
                                'additional_cost' => $data['additional_cost'][$key] ?? 0,
                                'additional_price' => $data['additional_price'][$key] ?? 0,
                                'qty' => 0,
                                'position' => $key + 1,
                            ]);
                        }
                        
                        $newProductVariantIds[] = $productVariant->id;
                        
                        // Ensure warehouse entries exist for this variant
                        $productWarehouses = ProductWarehouse::where([
                            'product_id' => $product->id,
                            'variant_id' => $variant->id
                        ])->get();
                        
                        if ($productWarehouses->isEmpty()) {
                            $warehouseIds = Warehouse::pluck('id')->toArray();
                            foreach ($warehouseIds as $wId) {
                                ProductWarehouse::firstOrCreate([
                                    "product_id" => $product->id,
                                    "variant_id" => $variant->id,
                                    "warehouse_id" => $wId,
                                    "qty" => 0,
                                ]);
                            }
                        }
                    }
                }
            } else {
                $data['is_variant'] = null;
                $data['variant_option'] = null;
                $data['variant_value'] = null;
            }
            
            // Delete old product variants that are no longer in the list
            foreach ($oldProductVariantIds as $productVariantId) {
                if (!in_array($productVariantId, $newProductVariantIds)) {
                    $productVariant = ProductVariant::find($productVariantId);
                    if ($productVariant && $productVariant->qty > 0) {
                        throw new HttpResponseException(
                            response()->json([
                                'message' => 'This variant has a quantity; you cannot delete it',
                            ], 422)
                        );
                    }
                    if ($productVariant) {
                        ProductWarehouse::where('product_id', $productVariant->product_id)
                            ->where('variant_id', $productVariant->variant_id)
                            ->delete();
                        $productVariant->delete();
                    }
                }
            }

            // Handle different prices per warehouse
            if (isset($data['is_diffPrice']) && isset($data['diff_price']) && is_array($data['diff_price'])) {
                foreach ($data['diff_price'] as $key => $diffPrice) {
                    if ($diffPrice) {
                        $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                            ->where('warehouse_id', $data['warehouse_id'][$key] ?? null)
                            ->whereNull('variant_id')
                            ->first();
                        
                        if ($productWarehouse) {
                            $productWarehouse->price = $diffPrice;
                            $productWarehouse->save();
                        } else {
                            ProductWarehouse::firstOrCreate([
                                "product_id" => $product->id,
                                "warehouse_id" => $data["warehouse_id"][$key] ?? null,
                                "qty" => 0,
                                "price" => $diffPrice
                            ]);
                        }
                    }
                }
            } elseif (isset($data['warehouse_id']) && is_array($data['warehouse_id'])) {
                foreach ($data['warehouse_id'] as $warehouseId) {
                    $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouseId)
                        ->whereNull('variant_id')
                        ->first();
                    
                    if ($productWarehouse) {
                        $productWarehouse->price = null;
                        $productWarehouse->save();
                    }
                }
            }

            // Handle slug for ecommerce
            $generalSetting = GeneralSetting::latest()->first();
            if ($generalSetting && in_array('ecommerce', explode(',', $generalSetting->modules ?? ''))) {
                if (!isset($data['slug'])) {
                    $data['slug'] = Str::slug($data['name'], '-');
                }
                $data['slug'] = preg_replace('/[^A-Za-z0-9\-]/', '', $data['slug']);
                $data['slug'] = str_replace('\/', '/', $data['slug']);
                
                if (isset($data['related_products'])) {
                    $data['related_products'] = is_array($data['related_products']) ? implode(",", $data['related_products']) : rtrim($data['related_products'], ",");
                }
                
                if (isset($data['in_stock'])) {
                    $data['in_stock'] = (bool)$data['in_stock'];
                } else {
                    $data['in_stock'] = false;
                }
                
                if (isset($data['is_online'])) {
                    $data['is_online'] = (bool)$data['is_online'];
                } else {
                    $data['is_online'] = false;
                }
            }

            // Handle restaurant module
            if ($generalSetting && in_array('restaurant', explode(',', $generalSetting->modules ?? ''))) {
                if (!isset($data['slug'])) {
                    $data['slug'] = Str::slug($data['name'], '-');
                }
                $data['slug'] = preg_replace('/[^A-Za-z0-9\-]/', '', $data['slug']);
                $data['slug'] = str_replace('\/', '/', $data['slug']);
                
                if (isset($data['related_products'])) {
                    $data['related_products'] = is_array($data['related_products']) ? implode(",", $data['related_products']) : rtrim($data['related_products'], ",");
                }
                if (isset($data['extras'])) {
                    $data['extras'] = is_array($data['extras']) ? implode(",", $data['extras']) : rtrim($data['extras'], ",");
                }
                
                if (isset($data['is_online'])) {
                    $data['is_online'] = (bool)$data['is_online'];
                } else {
                    $data['is_online'] = false;
                }
                
                if (isset($data['is_addon'])) {
                    $data['is_addon'] = (bool)$data['is_addon'];
                } else {
                    $data['is_addon'] = false;
                }
                
                if (isset($data['kitchen_id'])) {
                    $data['kitchen_id'] = $data['kitchen_id'];
                }
                
                if (isset($data['menu_type']) && is_array($data['menu_type'])) {
                    $data['menu_type'] = implode(",", $data['menu_type']);
                }
            }

            // Handle product details
            if (isset($data['product_details'])) {
                $data['product_details'] = str_replace('"', '@', $data['product_details']);
            }

            // Handle dates
            if (isset($data['starting_date'])) {
                $data['starting_date'] = date('Y-m-d', strtotime($data['starting_date']));
            }
            if (isset($data['last_date'])) {
                $data['last_date'] = date('Y-m-d', strtotime($data['last_date']));
            }

            // Handle default values
            if (!isset($data['featured'])) {
                $data['featured'] = false;
            }
            if (!isset($data['is_embeded'])) {
                $data['is_embeded'] = false;
            }
            if (!isset($data['promotion'])) {
                $data['promotion'] = null;
            }
            if (!isset($data['is_batch'])) {
                $data['is_batch'] = null;
            }
            if (!isset($data['is_imei'])) {
                $data['is_imei'] = null;
            }
            if (!isset($data['is_sync_disable']) && Schema::hasColumn('products', 'is_sync_disable')) {
                $data['is_sync_disable'] = null;
            }

            // Handle warranty and guarantee
            if (!isset($data['warranty'])) {
                $data['warranty'] = null;
                $data['warranty_type'] = null;
            }
            if (!isset($data['guarantee'])) {
                $data['guarantee'] = null;
                $data['guarantee_type'] = null;
            }

            // Update product
            $product->update($data);

            // Handle custom fields
            $this->handleCustomFields($product, $data);

            // Clear cache
            $this->cacheForget('product_list');
            $this->cacheForget('product_list_with_variant');

            return $product->fresh(['category', 'brand', 'unit']);
        });
    }

    /**
     * Delete a product (soft delete by setting is_active to false).
     *
     * @param Product $product Product instance to delete
     * @return bool
     */
    public function deleteProduct(Product $product): bool
    {
        return $this->transaction(function () use ($product) {
            $product->is_active = false;
            
            // Delete images
            if ($product->image && $product->image != 'zummXD2dvAtI.png') {
                $images = is_array($product->image) ? $product->image : explode(",", $product->image);
                foreach ($images as $image) {
                    $this->deleteImageFromStorage($image);
                }
            }
            
            $product->save();
            
            // Clear cache
            $this->cacheForget('product_list');
            $this->cacheForget('product_list_with_variant');
            
            return true;
        });
    }

    /**
     * Bulk delete products.
     *
     * @param array<int> $ids Array of product IDs to delete
     * @return int Number of products deleted
     */
    public function bulkDeleteProducts(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $product = Product::findOrFail($id);
                $this->deleteProduct($product);
                $deletedCount++;
            } catch (Exception $e) {
                $this->logError("Failed to delete product {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Normalize product data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @param bool $isUpdate Whether this is an update operation
     * @return array<string, mixed>
     */
    private function normalizeProductData(array $data, bool $isUpdate = false): array
    {
        // Handle name
        if (isset($data['name'])) {
            $data['name'] = preg_replace('/[\n\r]/', "<br>", htmlspecialchars(trim($data['name']), ENT_QUOTES));
        }

        // Handle boolean fields
        $booleanFields = [
            'is_active', 'is_batch', 'is_variant', 'is_diffPrice', 'is_imei', 
            'featured', 'is_addon', 'is_online', 'in_stock', 'track_inventory', 
            'is_sync_disable', 'is_recipe', 'is_embeded', 'promotion'
        ];
        
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = filter_var(
                    $data[$field],
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );
            } elseif (!$isUpdate && in_array($field, ['is_active', 'track_inventory'])) {
                $data[$field] = true;
            }
        }

        // Handle numeric fields
        $numericFields = [
            'cost', 'profit_margin', 'price', 'wholesale_price', 'qty', 
            'alert_quantity', 'daily_sale_objective', 'promotion_price', 
            'warranty', 'guarantee', 'wastage_percent', 'production_cost'
        ];
        
        foreach ($numericFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = is_numeric($data[$field]) ? (float)$data[$field] : null;
            }
        }

        // Handle integer fields
        $integerFields = [
            'brand_id', 'category_id', 'unit_id', 'purchase_unit_id', 'sale_unit_id',
            'tax_id', 'tax_method', 'kitchen_id', 'woocommerce_product_id', 
            'woocommerce_media_id', 'combo_unit_id'
        ];
        
        foreach ($integerFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = is_numeric($data[$field]) ? (int)$data[$field] : null;
            }
        }

        return $data;
    }

    /**
     * Handle image uploads and create multiple sizes.
     *
     * @param array<UploadedFile> $images
     * @param int $offset Offset for naming (used in updates)
     * @return array{paths: array<string>, urls: array<string>}
     */
    private function handleImageUploads(array $images, int $offset = 0): array
    {
        $this->ensureImageDirectoriesExist();
        
        $imagePaths = [];
        $imageUrls = [];
        
        foreach ($images as $key => $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }
            
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . ($offset + $key + 1);
            
            // Handle multi-tenant logic
            if (config('database.connections.saleprosaas_landlord', false)) {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            } else {
                $imageName = $imageName . '.' . $ext;
            }
            
            // Move original image
            $image->move(public_path('images/product'), $imageName);
            
            // Create different sizes
            $manager = new ImageManager(new GdDriver());
            $img = $manager->read(public_path('images/product/' . $imageName));
            
            $img->resize(1000, 1250)->save(public_path('images/product/xlarge/' . $imageName));
            $img->resize(500, 500)->save(public_path('images/product/large/' . $imageName));
            $img->resize(250, 250)->save(public_path('images/product/medium/' . $imageName));
            $img->resize(100, 100)->save(public_path('images/product/small/' . $imageName));
            
            $imagePaths[] = $imageName;
            $imageUrls[] = url('images/product', $imageName);
        }
        
        return [
            'paths' => $imagePaths,
            'urls' => $imageUrls
        ];
    }

    /**
     * Ensure image directories exist.
     *
     * @return void
     */
    private function ensureImageDirectoriesExist(): void
    {
        $directories = ['xlarge', 'large', 'medium', 'small'];
        foreach ($directories as $dir) {
            $path = public_path("images/product/{$dir}");
            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Delete image from storage (all sizes).
     *
     * @param string $imageName
     * @return void
     */
    private function deleteImageFromStorage(string $imageName): void
    {
        $sizes = ['', 'xlarge/', 'large/', 'medium/', 'small/'];
        foreach ($sizes as $size) {
            $path = public_path("images/product/{$size}{$imageName}");
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * Handle custom fields for products.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @return void
     */
    private function handleCustomFields(Product $product, array $data): void
    {
        $customFields = CustomField::where('belongs_to', 'product')->select('name', 'type')->get();
        $customFieldData = [];
        
        foreach ($customFields as $customField) {
            $fieldName = str_replace(' ', '_', strtolower($customField->name));
            if (isset($data[$fieldName])) {
                if ($customField->type == 'checkbox' || $customField->type == 'multi_select') {
                    $customFieldData[$fieldName] = is_array($data[$fieldName]) 
                        ? implode(",", $data[$fieldName]) 
                        : $data[$fieldName];
                } else {
                    $customFieldData[$fieldName] = $data[$fieldName];
                }
            }
        }
        
        if (!empty($customFieldData)) {
            DB::table('products')->where('id', $product->id)->update($customFieldData);
        }
    }

    /**
     * Handle product variants creation/update.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @return void
     */
    private function handleProductVariants(Product $product, array $data): void
    {
        if (!isset($data['variant_name']) || !is_array($data['variant_name'])) {
            return;
        }
        
        foreach ($data['variant_name'] as $key => $variantName) {
            $variant = Variant::firstOrCreate(['name' => $variantName]);
            
            ProductVariant::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                ],
                [
                    'item_code' => $data['item_code'][$key] ?? null,
                    'additional_cost' => $data['additional_cost'][$key] ?? 0,
                    'additional_price' => $data['additional_price'][$key] ?? 0,
                    'qty' => 0,
                    'position' => $key + 1,
                ]
            );
        }
    }

    /**
     * Auto purchase for initial stock.
     *
     * @param Product $product
     * @param int $warehouseId
     * @param float $stock
     * @return void
     */
    private function autoPurchase(Product $product, int $warehouseId, float $stock): void
    {
        // This is a simplified version - the full implementation would create Purchase records
        // For now, we'll just create the warehouse entry
        $productWarehouse = ProductWarehouse::where([
            ['product_id', $product->id],
            ['warehouse_id', $warehouseId]
        ])->first();
        
        if ($productWarehouse) {
            $productWarehouse->qty += $stock;
            $productWarehouse->save();
        } else {
            ProductWarehouse::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'qty' => $stock,
            ]);
        }
    }

    /**
     * Get products without variants.
     *
     * @return Collection<int, Product>
     */
    public function getProductsWithoutVariant(): Collection
    {
        return Product::where('is_active', true)
            ->where('type', 'standard')
            ->whereNull('is_variant')
            ->select('id', 'name', 'code')
            ->get();
    }

    /**
     * Get products with variants.
     *
     * @return Collection<int, Product>
     */
    public function getProductsWithVariant(): Collection
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id')
            ->where('products.is_active', true)
            ->where('products.type', 'standard')
            ->whereNotNull('products.is_variant')
            ->select('products.id', 'products.name', 'product_variants.item_code as code', 'product_variants.qty')
            ->orderBy('product_variants.position')
            ->get();
    }

    /**
     * Generate product code.
     *
     * @return string
     */
    public function generateCode(): string
    {
        return (string)rand(10000000, 99999999);
    }
}

