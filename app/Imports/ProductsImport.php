<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Unit;
use App\Models\Variant;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * ProductsImport
 *
 * Handles importing products from CSV/Excel files.
 */
class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Process the collection of rows.
     *
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            // Skip if name is empty
            if (empty($row['name'] ?? null)) {
                continue;
            }

            // Get basic product data
            $name = trim($row['name'] ?? '');
            $code = trim($row['code'] ?? '');
            $type = strtolower(trim($row['type'] ?? 'standard'));
            $cost = is_numeric($row['cost'] ?? null) ? (float) str_replace(',', '', (string) $row['cost']) : 0;
            $price = is_numeric($row['price'] ?? null) ? (float) str_replace(',', '', (string) $row['price']) : ($cost * 1.25); // Default 25% margin
            $profitMargin = is_numeric($row['profitmargin'] ?? null) ? (float) $row['profitmargin'] : 25;

            // Calculate price from cost and profit margin if price not provided
            if (!is_numeric($row['price'] ?? null) && is_numeric($row['profitmargin'] ?? null)) {
                $price = $cost * (1 + $profitMargin / 100);
            } elseif (is_numeric($row['price'] ?? null)) {
                // Calculate profit margin from price and cost
                $profitMargin = $cost > 0 ? (($price - $cost) / $cost) * 100 : 25;
            }

            // Handle brand
            $brandId = null;
            $brandName = trim($row['brand'] ?? '');
            if (!empty($brandName) && $brandName !== 'N/A') {
                $brand = Brand::firstOrCreate(
                    ['title' => $brandName, 'is_active' => true],
                    ['title' => $brandName, 'is_active' => true]
                );
                $brandId = $brand->id;
            }

            // Handle category
            $categoryName = trim($row['category'] ?? '');
            if (empty($categoryName)) {
                continue; // Category is required
            }
            $category = Category::firstOrCreate(
                ['name' => $categoryName, 'is_active' => true],
                ['name' => $categoryName, 'is_active' => true, 'slug' => Str::slug($categoryName)]
            );

            // Handle unit
            $unitCode = trim($row['unitcode'] ?? '');
            if (empty($unitCode)) {
                continue; // Unit code is required
            }
            $unit = Unit::where('unit_code', $unitCode)->first();
            if (!$unit) {
                continue; // Unit not found
            }

            // Create or update product
            $product = Product::firstOrNew(
                ['code' => $code, 'is_active' => true]
            );

            $product->fill([
                'name' => $name,
                'type' => in_array($type, ['standard', 'combo', 'digital', 'service']) ? $type : 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => $brandId,
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'purchase_unit_id' => $unit->id,
                'sale_unit_id' => $unit->id,
                'cost' => $cost,
                'profit_margin' => $profitMargin,
                'price' => $price,
                'tax_method' => 1, // Inclusive
                'qty' => 0,
                'product_details' => trim($row['productdetails'] ?? ''),
                'is_active' => true,
            ]);

            // Generate slug if not set
            if (!$product->slug) {
                $product->slug = Str::slug($name, '-');
            }

            $product->save();

            // Handle variants if provided
            $variantValue = trim($row['variantvalue'] ?? '');
            $variantName = trim($row['variantname'] ?? '');
            $itemCodes = !empty($row['itemcode'] ?? null) ? explode(',', (string) $row['itemcode']) : [];
            $additionalCosts = !empty($row['additionalcost'] ?? null) ? explode(',', (string) $row['additionalcost']) : [];
            $additionalPrices = !empty($row['additionalprice'] ?? null) ? explode(',', (string) $row['additionalprice']) : [];

            if (!empty($variantValue) && !empty($variantName)) {
                // Parse variant options and values
                $variantOptions = [];
                $variantValues = [];
                $variantInfo = explode(',', $variantValue);

                foreach ($variantInfo as $info) {
                    if (strpos($info, '[') === false) {
                        continue;
                    }
                    $variantOptions[] = trim(strtok($info, '['));
                    $variantValueStr = substr($info, strpos($info, '[') + 1, strpos($info, ']') - strpos($info, '[') - 1);
                    $variantValues[] = str_replace('/', ',', $variantValueStr);
                }

                if (!empty($variantOptions) && !empty($variantValues)) {
                    $product->variant_option = json_encode($variantOptions);
                    $product->variant_value = json_encode($variantValues);
                    $product->is_variant = true;
                    $product->save();

                    // Create product variants
                    $variantNames = explode(',', $variantName);
                    $warehouseIds = Warehouse::where('is_active', true)->pluck('id');

                    foreach ($variantNames as $key => $variantNameStr) {
                        $variant = Variant::firstOrCreate(['name' => trim($variantNameStr)]);

                        ProductVariant::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                            ],
                            [
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'position' => $key + 1,
                                'item_code' => $itemCodes[$key] ?? ($variant->name . '-' . $code),
                                'additional_cost' => isset($additionalCosts[$key]) ? (float) str_replace(',', '', $additionalCosts[$key]) : 0,
                                'additional_price' => isset($additionalPrices[$key]) ? (float) str_replace(',', '', $additionalPrices[$key]) : 0,
                                'qty' => 0,
                            ]
                        );

                        // Create product warehouse entries for variants
                        foreach ($warehouseIds as $warehouseId) {
                            ProductWarehouse::firstOrCreate(
                                [
                                    'product_id' => $product->id,
                                    'variant_id' => $variant->id,
                                    'warehouse_id' => $warehouseId,
                                ],
                                [
                                    'product_id' => $product->id,
                                    'variant_id' => $variant->id,
                                    'warehouse_id' => $warehouseId,
                                    'qty' => 0,
                                ]
                            );
                        }
                    }
                }
            } else {
                // Create product warehouse entries for non-variant products
                $warehouseIds = Warehouse::where('is_active', true)->pluck('id');
                foreach ($warehouseIds as $warehouseId) {
                    ProductWarehouse::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouseId,
                        ],
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouseId,
                            'qty' => 0,
                        ]
                    );
                }
            }
        }
    }
}

