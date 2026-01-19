<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Enums\TaxMethodEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\GeneralSetting;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductPurchase;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Variant;
use App\Models\Warehouse;
use App\Imports\ProductsImport;
use App\Traits\CheckPermissionsTrait;
use App\Traits\TenantInfo;
use App\Traits\CacheForget;
use App\Traits\GeneralSettingsTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

/**
 * ProductService
 *
 * Handles all business logic for product operations including CRUD operations,
 * filtering, pagination, variants, batches, warehouses, images, and bulk operations.
 * Enforces permission checks for all operations.
 */
class ProductService extends BaseService
{
    use CheckPermissionsTrait;
    use CacheForget;
    use TenantInfo;
    use GeneralSettingsTrait;

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
        // Check permission: user needs 'products-index' permission to view products
        $this->requirePermission('products-index');

        $warehouseId = $filters['warehouse_id'] ?? 0;
        $stockFilter = $filters['stock_filter'] ?? 'all';
        $isRecipe = $filters['is_recipe'] ?? false;

        // Base query with relations
        $query = Product::with(['category:id,name', 'brand:id,name', 'unit:id,name,code'])
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

        // Search functionality - using standard Laravel query builder
        if (!empty($filters['search'] ?? null)) {
            $search = $filters['search'];
            
            $query->where(function ($q) use ($search) {
                // Search in product name or code
                $q->where('products.name', 'LIKE', "%{$search}%")
                  ->orWhere('products.code', 'LIKE', "%{$search}%")
                  // Search in product variants
                  ->orWhereHas('productVariants', function ($variantQuery) use ($search) {
                      $variantQuery->where('item_code', 'LIKE', "%{$search}%");
                  })
                  // Search in brand name
                  ->orWhereHas('brand', function ($brandQuery) use ($search) {
                      $brandQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  // Search in category name
                  ->orWhereHas('category', function ($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'LIKE', "%{$search}%");
                  });
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
        // Check permission: user needs 'products-index' permission to view products
        $this->requirePermission('products-index');

        return Product::with([
            'category:id,name',
            'brand:id,name',
            'unit:id,name,code',
            'purchaseUnit:id,name,code',
            'saleUnit:id,name,code',
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
        // Check permission: user needs 'products-add' permission to create products
        $this->requirePermission('products-add');

        return $this->transaction(function () use ($data) {
            // Normalize data
            $data = $this->normalizeProductData($data, false);

            // Handle images
            if (isset($data['image']) && is_array($data['image'])) {
                $imagePaths = $this->handleImageUploads($data['image']);
                $data['image'] = $imagePaths['paths'];
                $data['image_url'] = $imagePaths['urls'];
            } else {
                $data['image'] = ['placeholder.png'];
                $data['image_url'] = null;
            }

            // Handle file upload (for digital products)
            if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                $filePath = $this->uploadService->upload(
                    $data['file'],
                    config('storage.products.files')
                );
                $data['file'] = $filePath;
                $data['file_url'] = $this->uploadService->url($filePath);
            }

            // Handle combo/recipe products
            if (($data['type'] == 'combo' || (isset($data['is_recipe']) && $data['is_recipe'] === true)) && isset($data['product_id'])) {
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

            // Handle menu type for restaurant
            $generalSetting = GeneralSetting::latest()->first();
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
            if (!isset($data['profit_margin_type'])) {
                $data['profit_margin_type'] = 'percentage';
            }
            if (!isset($data['is_batch'])) {
                $data['is_batch'] = null;
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
            if (isset($data['is_diff_price']) && isset($data['diff_price']) && is_array($data['diff_price'])) {
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
            } elseif (!isset($data['is_initial_stock']) && !isset($data['is_batch']) && config('without_stock') == 'yes') {
                // Create warehouse entries for variants if without_stock config is yes
                $warehouseIds = Warehouse::where('is_active', true)->pluck('id');
                $variantIds = [];
                
                // Track variant IDs if product has variants
                if (isset($data['is_variant']) && $data['is_variant']) {
                    $variantIds = ProductVariant::where('product_id', $product->id)->pluck('variant_id')->toArray();
                }
                
                foreach ($warehouseIds as $warehouseId) {
                    if (!empty($variantIds)) {
                        foreach ($variantIds as $variantId) {
                            ProductWarehouse::firstOrCreate([
                                "product_id" => $product->id,
                                "variant_id" => $variantId,
                                "warehouse_id" => $warehouseId,
                                "qty" => 0,
                            ]);
                        }
                    } else {
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
        // Check permission: user needs 'products-edit' permission to update products
        $this->requirePermission('products-edit');

        return $this->transaction(function () use ($product, $data) {
            // Normalize data
            $data = $this->normalizeProductData($data, true);

            // Handle images
            // First, extract new uploaded files from $data['image'] BEFORE processing prev_img
            $newUploadedImages = [];
            if (isset($data['image']) && is_array($data['image'])) {
                foreach ($data['image'] as $image) {
                    if ($image instanceof UploadedFile) {
                        $newUploadedImages[] = $image;
                    }
                }
            }

            // Get previous images from prev_img array
            $previousImages = [];
            if (isset($data['prev_img']) && is_array($data['prev_img'])) {
                foreach ($data['prev_img'] as $prevImg) {
                    if (!empty($prevImg) && !in_array($prevImg, $previousImages)) {
                        $previousImages[] = $prevImg;
                    }
                }
            }

            // Process new uploads if any
            if (!empty($newUploadedImages)) {
                $imagePaths = $this->handleImageUploads($newUploadedImages, count($previousImages));
                $data['image'] = array_merge($previousImages, $imagePaths['paths']);
                // Generate URLs for previous images and merge with new image URLs
                $previousImageUrls = array_map(function ($img) {
                    return $this->uploadService->url(config('storage.products.images.base') . '/' . $img);
                }, $previousImages);
                $data['image_url'] = array_merge($previousImageUrls, $imagePaths['urls']);
            } elseif (!empty($previousImages)) {
                // Only previous images, no new uploads
                $data['image'] = $previousImages;
                // Generate URLs for previous images
                $data['image_url'] = array_map(function ($img) {
                    return $this->uploadService->url(config('storage.products.images.base') . '/' . $img);
                }, $previousImages);
            } else {
                // No images provided, keep existing product images
                $data['image'] = $product->image ?? [];
                $data['image_url'] = $product->image_url ?? [];
            }

            // Handle file upload
            if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                // Delete old file if exists
                if ($product->file && $this->uploadService->exists($product->file)) {
                    $this->uploadService->delete($product->file);
                }
                $filePath = $this->uploadService->upload(
                    $data['file'],
                    config('storage.products.files')
                );
                $data['file'] = $filePath;
                $data['file_url'] = $this->uploadService->url($filePath);
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
            if (isset($data['is_diff_price']) && isset($data['diff_price']) && is_array($data['diff_price'])) {
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
            } else {
                // Clear is_diff_price flag when not set
                $data['is_diff_price'] = false;
                
                // Clear warehouse prices if warehouse_id is provided
                if (isset($data['warehouse_id']) && is_array($data['warehouse_id'])) {
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
            }

            // Handle ecommerce module fields
            $generalSetting = GeneralSetting::latest()->first();
            if ($generalSetting && in_array('ecommerce', explode(',', $generalSetting->modules ?? ''))) {
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

            // Handle restaurant module fields
            if ($generalSetting && in_array('restaurant', explode(',', $generalSetting->modules ?? ''))) {
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
            
            // Note: Slug generation is handled by Product model's boot() method

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
            if (!isset($data['profit_margin_type'])) {
                $data['profit_margin_type'] = 'percentage';
            }
            if (!isset($data['profit_margin'])) {
                $data['profit_margin'] = 0;
            }
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
        // Check permission: user needs 'products-delete' permission to delete products
        $this->requirePermission('products-delete');

        return $this->transaction(function () use ($product) {
            $product->is_active = false;
            
            // Delete images
            if ($product->image && $product->image != 'placeholder.png') {
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
        // Check permission: user needs 'products-delete' permission to delete products
        $this->requirePermission('products-delete');

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

        // Handle boolean fields (per model casts)
        // Note: Types are already normalized in ProductRequest::prepareForValidation()
        // This serves as a safeguard and handles defaults
        $booleanFields = [
            'is_active', 'is_batch', 'is_variant', 'is_diff_price', 'is_imei', 
            'featured', 'is_addon', 'is_online', 'in_stock', 'track_inventory', 
            'is_sync_disable', 'is_recipe', 'is_embeded', 'promotion'
        ];
        
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                // Only normalize if not already boolean (safeguard)
                if (!is_bool($data[$field])) {
                    $data[$field] = filter_var(
                        $data[$field],
                        FILTER_VALIDATE_BOOLEAN,
                        FILTER_NULL_ON_FAILURE
                    );
                }
            } elseif (!$isUpdate && in_array($field, ['is_active', 'track_inventory'])) {
                $data[$field] = true;
            }
        }

        $numericFields = [
            'cost', 'profit_margin', 'price', 'wholesale_price', 'qty', 
            'alert_quantity', 'daily_sale_objective', 'promotion_price', 
            'wastage_percent', 'production_cost'
        ];
        
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && !is_float($data[$field])) {
                $data[$field] = is_numeric($data[$field]) ? (float)$data[$field] : null;
            }
        }

        // Handle integer fields (per model casts)
        $integerFields = [
            'brand_id', 'category_id', 'unit_id', 'purchase_unit_id', 'sale_unit_id',
            'tax_id', 'tax_method', 'kitchen_id', 'woocommerce_product_id', 
            'woocommerce_media_id', 'combo_unit_id', 'warranty', 'guarantee'
        ];
        
        foreach ($integerFields as $field) {
            if (isset($data[$field]) && !is_int($data[$field])) {
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
        $disk = $this->getStorageProvider();
        $baseDirectory = config('storage.products.images.base');
        
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
            
            // Ensure base directory exists
            Storage::disk($disk)->makeDirectory($baseDirectory);
            
            // Store original image
            $originalPath = $baseDirectory . '/' . $imageName;
            Storage::disk($disk)->putFileAs($baseDirectory, $image, $imageName);
            
            // Create different sizes using Intervention Image
            $manager = new ImageManager(new GdDriver());
            $img = $manager->read($image->getRealPath());
            
            // Create size directories and save resized images
            $sizes = [
                'xlarge' => [1000, 1250],
                'large' => [500, 500],
                'medium' => [250, 250],
                'small' => [100, 100],
            ];
            
            foreach ($sizes as $sizeName => [$width, $height]) {
                $sizeDirectory = config("storage.products.images.{$sizeName}");
                Storage::disk($disk)->makeDirectory($sizeDirectory);
                
                $resized = clone $img;
                $resized->resize($width, $height);
                
                // Save to temporary file first, then put to storage
                $tempPath = sys_get_temp_dir() . '/' . uniqid() . '_' . $imageName;
                $resized->save($tempPath);
                
                Storage::disk($disk)->put($sizeDirectory . '/' . $imageName, file_get_contents($tempPath));
                unlink($tempPath);
            }
            
            // Store just the filename (not full path) for database compatibility
            $imagePaths[] = $imageName;
            $imageUrls[] = $this->uploadService->url($originalPath);
        }
        
        return [
            'paths' => $imagePaths,
            'urls' => $imageUrls
        ];
    }

    /**
     * Delete image from storage (all sizes).
     *
     * @param string $imageName
     * @return void
     */
    private function deleteImageFromStorage(string $imageName): void
    {
        $disk = $this->getStorageProvider();
        $baseDirectory = config('storage.products.images.base');
        
        // Delete from base directory and all size directories
        $sizes = [
            '' => $baseDirectory,
            'xlarge' => config('storage.products.images.xlarge'),
            'large' => config('storage.products.images.large'),
            'medium' => config('storage.products.images.medium'),
            'small' => config('storage.products.images.small'),
        ];
        
        foreach ($sizes as $sizeDirectory) {
            $path = $sizeDirectory . '/' . $imageName;
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
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
     * Creates a Purchase record with ProductPurchase pivot and Payment for initial stock.
     * This ensures proper accounting and inventory tracking.
     *
     * @param Product $product
     * @param int $warehouseId
     * @param float $stock
     * @return void
     */
    private function autoPurchase(Product $product, int $warehouseId, float $stock): void
    {
        // Prepare purchase data
        $userId = Auth::check() ? Auth::id() : 1; // Fallback to 1 if no authenticated user
        $purchasePrefix = config('references.purchase.prefix', 'pr');
        $purchaseData = [
            'reference_no' => $purchasePrefix . '-' . date("Ymd") . '-' . date("his"),
            'user_id' => $userId,
            'warehouse_id' => $warehouseId,
            'supplier_id' => 0, // No supplier for auto purchase
            'item' => 1,
            'total_qty' => $stock,
            'total_discount' => 0,
            'status' => PurchaseStatusEnum::COMPLETED->value,
            'payment_status' => PaymentStatusEnum::PAID->value,
        ];

        // Calculate tax and costs based on tax method
        $taxRate = 0.00;
        $tax = 0.00;
        $netUnitCost = number_format((float)$product->cost, 2, '.', '');
        
        if ($product->tax_id) {
            $taxData = Tax::find($product->tax_id);
            if ($taxData) {
                $taxRate = $taxData->rate;
                
                if ($product->tax_method == TaxMethodEnum::EXCLUSIVE->value) {
                    // Exclusive tax: tax is added on top
                    $netUnitCost = number_format((float)$product->cost, 2, '.', '');
                    $tax = number_format((float)$product->cost * $stock * ($taxRate / 100), 2, '.', '');
                    $cost = number_format((float)$product->cost * $stock + $tax, 2, '.', '');
                } else {
                    // Inclusive tax: tax is included in the cost
                    $netUnitCost = number_format((100 / (100 + $taxRate)) * (float)$product->cost, 2, '.', '');
                    $tax = number_format(((float)$product->cost - $netUnitCost) * $stock, 2, '.', '');
                    $cost = number_format((float)$product->cost * $stock, 2, '.', '');
                }
                
                $purchaseData['total_tax'] = $tax;
                $purchaseData['total_cost'] = $cost;
            }
        } else {
            // No tax
            $purchaseData['total_tax'] = 0.00;
            $purchaseData['total_cost'] = number_format((float)$product->cost * $stock, 2, '.', '');
            $cost = $purchaseData['total_cost'];
        }

        // Update or create ProductWarehouse entry
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

        // Set additional purchase fields
        $purchaseData['order_tax'] = 0;
        $purchaseData['grand_total'] = $purchaseData['total_cost'];
        $purchaseData['paid_amount'] = $purchaseData['grand_total'];

        // Create Purchase record
        $purchase = Purchase::create($purchaseData);

        // Create ProductPurchase pivot record
        ProductPurchase::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'qty' => $stock,
            'recieved' => $stock,
            'purchase_unit_id' => $product->unit_id ?? $product->purchase_unit_id ?? 1,
            'net_unit_cost' => $netUnitCost,
            'net_unit_price' => $netUnitCost, // Same as cost for auto purchase
            'discount' => 0,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'total' => $cost ?? $purchaseData['total_cost'],
        ]);

        // Create Payment record
        $paymentPrefix = config('references.payment.purchase', 'ppr');
        Payment::create([
            'payment_reference' => $paymentPrefix . '-' . date("Ymd") . '-' . date("his"),
            'user_id' => $userId,
            'purchase_id' => $purchase->id,
            'account_id' => 0, // Default account
            'amount' => $purchaseData['grand_total'],
            'change' => 0,
            'paying_method' => PaymentMethodEnum::CASH->value,
            'payment_at' => now(),
        ]);
    }

    /**
     * Get products without variants.
     *
     * @return Collection<int, Product>
     */
    public function getProductsWithoutVariant(): Collection
    {
        // Check permission: user needs 'products-index' permission to view products
        $this->requirePermission('products-index');

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
        // Check permission: user needs 'products-index' permission to view products
        $this->requirePermission('products-index');

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
        // Check permission: user needs 'products-add' permission to generate product codes
        $this->requirePermission('products-add');

        return (string)rand(10000000, 99999999);
    }

    /**
     * Import products from a file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importProducts(UploadedFile $file): void
    {
        // Check permission: user needs 'products-add' permission to import products
        $this->requirePermission('products-add');

        $this->transaction(function () use ($file) {
            Excel::import(new ProductsImport(), $file);
        });
    }

    /**
     * Reorder product images.
     *
     * @param Product $product
     * @param array<string> $imageUrls Array of image URLs in the new order
     * @return Product
     */
    public function reorderImages(Product $product, array $imageUrls): Product
    {
        // Check permission: user needs 'products-edit' permission to reorder images
        $this->requirePermission('products-edit');

        // Extract filenames from URLs
        $imageFilenames = [];
        foreach ($imageUrls as $url) {
            // Extract filename from URL (e.g., "http://domain.com/images/product/filename.jpg" -> "filename.jpg")
            $filename = basename(parse_url($url, PHP_URL_PATH));
            if ($filename && in_array($filename, $product->image ?? [])) {
                $imageFilenames[] = $filename;
            }
        }

        // Only update if we have valid filenames
        if (count($imageFilenames) === count($product->image ?? [])) {
            // Generate new image_url array in the new order
            $newImageUrls = [];
            foreach ($imageFilenames as $filename) {
                $newImageUrls[] = $this->uploadService->url(config('storage.products.images.base') . '/' . $filename);
            }

            $product->image = $imageFilenames;
            $product->image_url = $newImageUrls;
            $product->save();
        }

        return $product->fresh();
    }

    /**
     * Search products by name or code (for related products and extras).
     *
     * @param string $term Search term (minimum 3 characters)
     * @return SupportCollection<int, array{id: int, name: string, code: string, image: string}>
     */
    public function searchProducts(string $term): SupportCollection
    {
        // Check permission: user needs 'products-index' permission to search products
        $this->requirePermission('products-index');

        if (strlen($term) < 3) {
            return collect([]);
        }

        $products = Product::where('is_active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('code', 'LIKE', "%{$term}%");
            })
            ->select('id', 'name', 'code', 'image_url')
            ->limit(50)
            ->get();

        return $products->map(function ($product) {
            $imageUrl = $product->image_url ? (is_array($product->image_url) ? $product->image_url[0] : (is_string($product->image_url) ? explode(',', $product->image_url)[0] : null)) : null;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'image_url' => $imageUrl ?? 'placeholder.png',
            ];
        });
    }

    /**
     * Get sale and purchase units based on base unit ID.
     *
     * @param int $unitId Base unit ID
     * @return array<int, string>
     */
    public function getSaleUnits(int $unitId): array
    {
        // Check permission: user needs 'products-index' permission to get sale units
        $this->requirePermission('products-index');

        $units = Unit::where(function ($query) use ($unitId) {
                $query->where('base_unit', $unitId)
                    ->orWhere('id', $unitId);
            })
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();

        return $units;
    }

    /**
     * Search product for combo products table.
     * Returns detailed product information including variant support.
     *
     * @param string $searchTerm Product code or item code (may include variant info in format "code (name)")
     * @return array<string, mixed>|null
     */
    public function searchComboProduct(string $searchTerm): ?array
    {
        // Check permission: user needs 'products-index' permission to search combo products
        $this->requirePermission('products-index');

        // Extract product code (remove everything after " (" or "|")
        $productCode = trim(explode('(', $searchTerm)[0]);
        $productCode = trim(explode('|', $productCode)[0]);

        // First try to find product by code
        $product = Product::where('code', $productCode)
            ->where('is_active', true)
            ->first();

        $productId = $product ? $product->id : null;
        $isVariantProduct = $product && $product->is_variant;

        // If not found or product has variants, check variants
        if (!$product || $isVariantProduct) {
            $productVariant = ProductVariant::join('products', 'product_variants.product_id', '=', 'products.id')
                ->where(function ($query) use ($productCode, $productId) {
                    $query->where('product_variants.item_code', $productCode);
                    if ($productId) {
                        $query->orWhere('product_variants.product_id', $productId);
                    }
                })
                ->where('products.is_active', true)
                ->select(
                    'products.*',
                    'product_variants.item_code',
                    'product_variants.variant_id',
                    'product_variants.additional_price',
                    'product_variants.cost as variant_cost'
                )
                ->first();

            if ($productVariant) {
                $product = $productVariant;
                $variantId = $productVariant->variant_id;
                $itemCode = $productVariant->item_code;
                $additionalPrice = $productVariant->additional_price ?? 0;
                $cost = $productVariant->variant_cost ?? $productVariant->cost ?? 0;
            } else {
                return null;
            }
        } else {
            $variantId = null;
            $itemCode = $product->code;
            $additionalPrice = 0;
            $cost = $product->cost ?? 0;
        }

        if (!$product) {
            return null;
        }

        // Get brand name
        $brand = $product->brand;
        $brandName = $brand ? $brand->name : null;

        // Get unit options as structured data
        $units = Unit::where(function ($query) use ($product) {
                $query->where('base_unit', $product->unit_id)
                    ->orWhere('id', $product->unit_id);
            })
            ->where('is_active', true)
            ->get()
            ->map(function ($unit) use ($product) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'operation_value' => $unit->operation_value ?? 1,
                    'operator' => $unit->operator ?? '*',
                    'selected' => $product->unit_id == $unit->id,
                ];
            })
            ->toArray();

        $price = ($product->price ?? 0) + $additionalPrice;

        // Return structured data
        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $itemCode,
            'price' => $price,
            'promotion_price' => $product->promotion_price ?? 0,
            'qty' => $product->qty ?? 0,
            'variant_id' => $variantId,
            'cost' => $cost,
            'brand' => $brandName,
            'unit_id' => $product->unit_id ?? null,
            'units' => $units,
            'additional_price' => $additionalPrice,
        ];
    }

    /**
     * Get sale history for a product.
     *
     * @param int $productId
     * @param array<string, mixed> $filters Available filters: warehouse_id, starting_date, ending_date, search, limit, offset
     * @return array<string, mixed> History data with pagination metadata
     */
    public function getSaleHistory(int $productId, array $filters = []): array
    {
        // Check permission: user needs 'product_history' permission to view product history
        $this->requirePermission('product_history');

        $warehouseId = $filters['warehouse_id'] ?? 0;
        $startingDate = $filters['starting_date'] ?? date('Y-m-d', strtotime('-1 year'));
        $endingDate = $filters['ending_date'] ?? date('Y-m-d');
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? 10;
        $offset = $filters['offset'] ?? 0;

        $query = DB::table('sales')
            ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
            ->whereNull('sales.deleted_at')
            ->where('product_sales.product_id', $productId)
            ->whereDate('sales.created_at', '>=', $startingDate)
            ->whereDate('sales.created_at', '<=', $endingDate);

        if ($warehouseId) {
            $query->where('sales.warehouse_id', $warehouseId);
        }

        // Apply staff access filter
        $generalSetting = $this->getGeneralSettings();
        if (Auth::check() && $generalSetting && $generalSetting->staff_access === 'own') {
            $query->where('sales.user_id', Auth::id());
        }

        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereDate('sales.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orWhere('sales.reference_no', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Join related tables and apply pagination
        $sales = $query->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->select(
                'sales.id',
                'sales.reference_no',
                'sales.created_at',
                'customers.name as customer_name',
                'customers.phone_number as customer_number',
                'warehouses.name as warehouse_name',
                'product_sales.qty',
                'product_sales.sale_unit_id',
                'product_sales.total'
            )
            ->offset($offset)
            ->limit($limit)
            ->orderBy('sales.created_at', 'desc')
            ->get();

        $data = [];
        $generalSetting = $this->getGeneralSettings();
        $decimal = $generalSetting->decimal ?? 2;

        foreach ($sales as $key => $sale) {
            $unit = $sale->sale_unit_id ? Unit::find($sale->sale_unit_id) : null;
            $qtyDisplay = number_format((float)$sale->qty, $decimal);
            if ($unit) {
                $qtyDisplay .= ' ' . $unit->code;
            }

            $data[] = [
                'id' => $sale->id,
                'key' => $key,
                'date' => date(config('date_format', 'Y-m-d'), strtotime($sale->created_at)),
                'reference_no' => $sale->reference_no,
                'warehouse' => $sale->warehouse_name,
                'customer' => $sale->customer_name . ' [' . ($sale->customer_number ?? 'N/A') . ']',
                'qty' => $qtyDisplay,
                'unit_price' => number_format((float)$sale->total / (float)$sale->qty, $decimal),
                'sub_total' => number_format((float)$sale->total, $decimal),
            ];
        }

        return [
            'data' => $data,
            'records_total' => $totalData,
            'records_filtered' => $totalFiltered,
        ];
    }

    /**
     * Get purchase history for a product.
     *
     * @param int $productId
     * @param array<string, mixed> $filters Available filters: warehouse_id, starting_date, ending_date, search, limit, offset
     * @return array<string, mixed> History data with pagination metadata
     */
    public function getPurchaseHistory(int $productId, array $filters = []): array
    {
        $warehouseId = $filters['warehouse_id'] ?? 0;
        $startingDate = $filters['starting_date'] ?? date('Y-m-d', strtotime('-1 year'));
        $endingDate = $filters['ending_date'] ?? date('Y-m-d');
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? 10;
        $offset = $filters['offset'] ?? 0;

        $query = DB::table('purchases')
            ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
            ->whereNull('purchases.deleted_at')
            ->where('product_purchases.product_id', $productId)
            ->whereDate('purchases.created_at', '>=', $startingDate)
            ->whereDate('purchases.created_at', '<=', $endingDate);

        if ($warehouseId) {
            $query->where('purchases.warehouse_id', $warehouseId);
        }

        // Apply staff access filter
        $generalSetting = $this->getGeneralSettings();
        if (Auth::check() && $generalSetting && $generalSetting->staff_access === 'own') {
            $query->where('purchases.user_id', Auth::id());
        }

        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereDate('purchases.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orWhere('purchases.reference_no', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Join related tables and apply pagination
        $purchases = $query->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->select(
                'purchases.id',
                'purchases.reference_no',
                'purchases.created_at',
                'purchases.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.phone_number as supplier_number',
                'warehouses.name as warehouse_name',
                'product_purchases.qty',
                'product_purchases.purchase_unit_id',
                'product_purchases.total'
            )
            ->offset($offset)
            ->limit($limit)
            ->orderBy('purchases.created_at', 'desc')
            ->get();

        $data = [];
        $generalSetting = $this->getGeneralSettings();
        $decimal = $generalSetting->decimal ?? 2;

        foreach ($purchases as $key => $purchase) {
            $unit = $purchase->purchase_unit_id ? Unit::find($purchase->purchase_unit_id) : null;
            $qtyDisplay = number_format((float)$purchase->qty, $decimal);
            if ($unit) {
                $qtyDisplay .= ' ' . $unit->code;
            }

            $data[] = [
                'id' => $purchase->id,
                'key' => $key,
                'date' => date(config('date_format', 'Y-m-d'), strtotime($purchase->created_at)),
                'reference_no' => $purchase->reference_no,
                'warehouse' => $purchase->warehouse_name,
                'supplier' => $purchase->supplier_id
                    ? ($purchase->supplier_name . ' [' . ($purchase->supplier_number ?? 'N/A') . ']')
                    : 'N/A',
                'qty' => $qtyDisplay,
                'unit_cost' => number_format((float)$purchase->total / (float)$purchase->qty, $decimal),
                'sub_total' => number_format((float)$purchase->total, $decimal),
            ];
        }

        return [
            'data' => $data,
            'records_total' => $totalData,
            'records_filtered' => $totalFiltered,
        ];
    }

    /**
     * Get sale return history for a product.
     *
     * @param int $productId
     * @param array<string, mixed> $filters Available filters: warehouse_id, starting_date, ending_date, search, limit, offset
     * @return array<string, mixed> History data with pagination metadata
     */
    public function getSaleReturnHistory(int $productId, array $filters = []): array
    {
        // Check permission: user needs 'product_history' permission to view product history
        $this->requirePermission('product_history');

        $warehouseId = $filters['warehouse_id'] ?? 0;
        $startingDate = $filters['starting_date'] ?? date('Y-m-d', strtotime('-1 year'));
        $endingDate = $filters['ending_date'] ?? date('Y-m-d');
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? 10;
        $offset = $filters['offset'] ?? 0;

        $query = DB::table('returns')
            ->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
            ->where('product_returns.product_id', $productId)
            ->whereDate('returns.created_at', '>=', $startingDate)
            ->whereDate('returns.created_at', '<=', $endingDate);

        if ($warehouseId) {
            $query->where('returns.warehouse_id', $warehouseId);
        }

        // Apply staff access filter
        $generalSetting = $this->getGeneralSettings();
        if (Auth::check() && $generalSetting && $generalSetting->staff_access === 'own') {
            $query->where('returns.user_id', Auth::id());
        }

        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereDate('returns.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orWhere('returns.reference_no', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Join related tables and apply pagination
        $returns = $query->join('customers', 'returns.customer_id', '=', 'customers.id')
            ->join('warehouses', 'returns.warehouse_id', '=', 'warehouses.id')
            ->select(
                'returns.id',
                'returns.reference_no',
                'returns.created_at',
                'customers.name as customer_name',
                'customers.phone_number as customer_number',
                'warehouses.name as warehouse_name',
                'product_returns.qty',
                'product_returns.sale_unit_id',
                'product_returns.total'
            )
            ->offset($offset)
            ->limit($limit)
            ->orderBy('returns.created_at', 'desc')
            ->get();

        $data = [];
        $generalSetting = $this->getGeneralSettings();
        $decimal = $generalSetting->decimal ?? 2;

        foreach ($returns as $key => $return) {
            $unit = $return->sale_unit_id ? Unit::find($return->sale_unit_id) : null;
            $qtyDisplay = number_format((float)$return->qty, $decimal);
            if ($unit) {
                $qtyDisplay .= ' ' . $unit->code;
            }

            $data[] = [
                'id' => $return->id,
                'key' => $key,
                'date' => date(config('date_format', 'Y-m-d'), strtotime($return->created_at)),
                'reference_no' => $return->reference_no,
                'warehouse' => $return->warehouse_name,
                'customer' => $return->customer_name . ' [' . ($return->customer_number ?? 'N/A') . ']',
                'qty' => $qtyDisplay,
                'unit_price' => number_format((float)$return->total / (float)$return->qty, $decimal),
                'sub_total' => number_format((float)$return->total, $decimal),
            ];
        }

        return [
            'data' => $data,
            'records_total' => $totalData,
            'records_filtered' => $totalFiltered,
        ];
    }

    /**
     * Get purchase return history for a product.
     *
     * @param int $productId
     * @param array<string, mixed> $filters Available filters: warehouse_id, starting_date, ending_date, search, limit, offset
     * @return array<string, mixed> History data with pagination metadata
     */
    public function getPurchaseReturnHistory(int $productId, array $filters = []): array
    {
        // Check permission: user needs 'product_history' permission to view product history
        $this->requirePermission('product_history');

        $warehouseId = $filters['warehouse_id'] ?? 0;
        $startingDate = $filters['starting_date'] ?? date('Y-m-d', strtotime('-1 year'));
        $endingDate = $filters['ending_date'] ?? date('Y-m-d');
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? 10;
        $offset = $filters['offset'] ?? 0;

        $query = DB::table('return_purchases')
            ->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
            ->where('purchase_product_return.product_id', $productId)
            ->whereDate('return_purchases.created_at', '>=', $startingDate)
            ->whereDate('return_purchases.created_at', '<=', $endingDate);

        if ($warehouseId) {
            $query->where('return_purchases.warehouse_id', $warehouseId);
        }

        // Apply staff access filter
        $generalSetting = $this->getGeneralSettings();
        if (Auth::check() && $generalSetting && $generalSetting->staff_access === 'own') {
            $query->where('return_purchases.user_id', Auth::id());
        }

        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereDate('return_purchases.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orWhere('return_purchases.reference_no', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Join related tables and apply pagination
        $returnPurchases = $query->leftJoin('suppliers', 'return_purchases.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'return_purchases.warehouse_id', '=', 'warehouses.id')
            ->select(
                'return_purchases.id',
                'return_purchases.reference_no',
                'return_purchases.created_at',
                'return_purchases.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.phone_number as supplier_number',
                'warehouses.name as warehouse_name',
                'purchase_product_return.qty',
                'purchase_product_return.purchase_unit_id',
                'purchase_product_return.total'
            )
            ->offset($offset)
            ->limit($limit)
            ->orderBy('return_purchases.created_at', 'desc')
            ->get();

        $data = [];
        $generalSetting = $this->getGeneralSettings();
        $decimal = $generalSetting->decimal ?? 2;

        foreach ($returnPurchases as $key => $returnPurchase) {
            $unit = $returnPurchase->purchase_unit_id ? Unit::find($returnPurchase->purchase_unit_id) : null;
            $qtyDisplay = number_format((float)$returnPurchase->qty, $decimal);
            if ($unit) {
                $qtyDisplay .= ' ' . $unit->code;
            }

            $data[] = [
                'id' => $returnPurchase->id,
                'key' => $key,
                'date' => date(config('date_format', 'Y-m-d'), strtotime($returnPurchase->created_at)),
                'reference_no' => $returnPurchase->reference_no,
                'warehouse' => $returnPurchase->warehouse_name,
                'supplier' => $returnPurchase->supplier_id
                    ? ($returnPurchase->supplier_name . ' [' . ($returnPurchase->supplier_number ?? 'N/A') . ']')
                    : 'N/A',
                'qty' => $qtyDisplay,
                'unit_cost' => number_format((float)$returnPurchase->total / (float)$returnPurchase->qty, $decimal),
                'sub_total' => number_format((float)$returnPurchase->total, $decimal),
            ];
        }

        return [
            'data' => $data,
            'records_total' => $totalData,
            'records_filtered' => $totalFiltered,
        ];
    }

    /**
     * Get adjustment history for a product.
     *
     * @param int $productId
     * @param array<string, mixed> $filters Available filters: warehouse_id, starting_date, ending_date
     * @return array<string, mixed> History data
     */
    public function getAdjustmentHistory(int $productId, array $filters = []): array
    {
        // Check permission: user needs 'product_history' permission to view product history
        $this->requirePermission('product_history');

        $warehouseId = $filters['warehouse_id'] ?? 0;
        $startingDate = $filters['starting_date'] ?? date('Y-m-d', strtotime('-1 year'));
        $endingDate = $filters['ending_date'] ?? date('Y-m-d');

        $query = DB::table('adjustments as a')
            ->join('product_adjustments as pa', 'pa.adjustment_id', '=', 'a.id')
            ->where('pa.product_id', $productId)
            ->whereBetween(DB::raw('DATE(a.created_at)'), [$startingDate, $endingDate]);

        if ($warehouseId) {
            $query->where('a.warehouse_id', $warehouseId);
        }

        $rows = $query->select(
            'a.created_at',
            'a.reference_no',
            'a.warehouse_id',
            'pa.qty',
            'pa.action',
            'a.note'
        )
            ->orderBy('a.created_at', 'desc')
            ->get();

        $warehouses = Warehouse::pluck('name', 'id');
        $generalSetting = $this->getGeneralSettings();
        $decimal = $generalSetting->decimal ?? 2;

        $data = [];
        $key = 1;

        foreach ($rows as $row) {
            $data[] = [
                'key' => $key++,
                'date' => date(config('date_format', 'Y-m-d'), strtotime($row->created_at)),
                'reference' => $row->reference_no,
                'warehouse' => $warehouses[$row->warehouse_id] ?? '',
                'qty' => number_format((float)$row->qty, $decimal),
                'type' => $row->action === 'addition' ? 'Adjustment +' : 'Adjustment -',
                'note' => $row->note ?? 'N/A',
            ];
        }

        return [
            'data' => $data,
            'records_total' => count($data),
            'records_filtered' => count($data),
        ];
    }

    /**
     * Get transfer history for a product.
     *
     * @param int $productId
     * @param array<string, mixed> $filters Available filters: warehouse_id, starting_date, ending_date
     * @return array<string, mixed> History data
     */
    public function getTransferHistory(int $productId, array $filters = []): array
    {
        // Check permission: user needs 'product_history' permission to view product history
        $this->requirePermission('product_history');

        $warehouseId = $filters['warehouse_id'] ?? 0;
        $startingDate = $filters['starting_date'] ?? date('Y-m-d', strtotime('-1 year'));
        $endingDate = $filters['ending_date'] ?? date('Y-m-d');

        $query = DB::table('transfers as t')
            ->join('product_transfer as pt', 'pt.transfer_id', '=', 't.id')
            ->where('pt.product_id', $productId)
            ->whereBetween(DB::raw('DATE(t.created_at)'), [$startingDate, $endingDate]);

        if ($warehouseId) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('t.from_warehouse_id', $warehouseId)
                    ->orWhere('t.to_warehouse_id', $warehouseId);
            });
        }

        $rows = $query->select(
            't.created_at',
            't.reference_no',
            't.from_warehouse_id',
            't.to_warehouse_id',
            'pt.qty',
            't.note'
        )
            ->orderBy('t.created_at', 'desc')
            ->get();

        $warehouses = Warehouse::pluck('name', 'id');
        $generalSetting = $this->getGeneralSettings();
        $decimal = $generalSetting->decimal ?? 2;

        $data = [];
        $key = 1;

        foreach ($rows as $row) {
            // OUT transaction (from warehouse)
            if (!$warehouseId || $row->from_warehouse_id == $warehouseId) {
                $data[] = [
                    'key' => $key++,
                    'date' => date(config('date_format', 'Y-m-d'), strtotime($row->created_at)),
                    'reference' => $row->reference_no,
                    'from' => $warehouses[$row->from_warehouse_id] ?? '',
                    'to' => $warehouses[$row->to_warehouse_id] ?? '',
                    'qty' => number_format(-(float)$row->qty, $decimal),
                ];
            }

            // IN transaction (to warehouse)
            if (!$warehouseId || $row->to_warehouse_id == $warehouseId) {
                $data[] = [
                    'key' => $key++,
                    'date' => date(config('date_format', 'Y-m-d'), strtotime($row->created_at)),
                    'reference' => $row->reference_no,
                    'from' => $warehouses[$row->from_warehouse_id] ?? '',
                    'to' => $warehouses[$row->to_warehouse_id] ?? '',
                    'qty' => number_format((float)$row->qty, $decimal),
                ];
            }
        }

        return [
            'data' => $data,
            'records_total' => count($data),
            'records_filtered' => count($data),
        ];
    }
}

