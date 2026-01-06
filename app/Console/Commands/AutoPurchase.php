<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PosSetting;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\Tax;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Auto Purchase Command
 *
 * Automatically creates purchase orders for products that have fallen below
 * their alert quantity threshold. This command should be scheduled to run
 * periodically (e.g., daily) to maintain inventory levels.
 *
 * @package App\Console\Commands
 */
class AutoPurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically create purchase orders for products below alert quantity';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function handle(): int
    {
        try {
            $this->info('Starting automatic purchase process...');

            $products = $this->getProductsBelowAlertQuantity();

            if ($products->isEmpty()) {
                $this->info('No products require automatic purchase.');
                return Command::SUCCESS;
            }

            $this->info("Found {$products->count()} products requiring purchase.");

            $purchaseData = $this->preparePurchaseData($products);

            if (empty($purchaseData)) {
                $this->error('Failed to prepare purchase data.');
                return Command::FAILURE;
            }

            DB::transaction(function () use ($purchaseData, $products) {
                $purchase = Purchase::create($purchaseData);

                $this->createProductPurchases($purchase->id, $purchaseData, $products);
                $this->updateProductWarehouseStock($products, $purchaseData['warehouse_id']);
            });

            $this->info('Automatic purchase completed successfully.');
            return Command::SUCCESS;
        } catch (Exception $e) {
            Log::error('AutoPurchase: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Get products that are below alert quantity.
     *
     * @return Collection<Product>
     */
    private function getProductsBelowAlertQuantity()
    {
        return Product::where('is_active', true)
            ->whereColumn('alert_quantity', '>', 'qty')
            ->whereNull('is_variant')
            ->whereNull('is_batch')
            ->get();
    }

    /**
     * Prepare purchase data from products.
     *
     * @param Collection<Product> $products
     * @return array<string, mixed>|null Purchase data or null on failure
     */
    private function preparePurchaseData($products): ?array
    {
        $posSetting = PosSetting::latest()->first();
        $adminUser = User::where('is_active', true)
            ->where('role_id', 1)
            ->first();

        if (!$posSetting || !$adminUser) {
            $this->error('POS setting or admin user not found.');
            return null;
        }

        $referenceNo = 'pr-' . date('Ymd') . '-' . date('his');
        $totalQty = 10 * $products->count();
        $totalCost = 0;
        $totalTax = 0;

        $productData = [];
        foreach ($products as $key => $product) {
            $taxData = $product->tax_id ? Tax::find($product->tax_id) : null;
            $qty = 10;

            $calculation = $this->calculateProductCost($product, $taxData, $qty);
            $totalCost += $calculation['cost'];
            $totalTax += $calculation['tax'];

            $productData[$key] = [
                'product_id' => $product->id,
                'unit_id' => $product->unit_id,
                'net_unit_cost' => $calculation['net_unit_cost'],
                'tax_rate' => $calculation['tax_rate'],
                'tax' => $calculation['tax'],
                'total' => $calculation['cost'],
            ];
        }

        return [
            'reference_no' => $referenceNo,
            'user_id' => $adminUser->id,
            'warehouse_id' => $posSetting->warehouse_id,
            'item' => $products->count(),
            'total_qty' => $totalQty,
            'total_discount' => 0,
            'paid_amount' => 0,
            'status' => 1,
            'payment_status' => 1,
            'total_tax' => $totalTax,
            'total_cost' => $totalCost,
            'order_tax' => 0,
            'grand_total' => $totalCost,
            'product_data' => $productData,
        ];
    }

    /**
     * Calculate product cost including tax.
     *
     * @param Product $product
     * @param Tax|null $taxData
     * @param int $qty
     * @return array<string, float> Calculation results
     */
    private function calculateProductCost(Product $product, ?Tax $taxData, int $qty): array
    {
        $unitCost = (float)$product->cost;
        $taxRate = 0.0;
        $tax = 0.0;
        $netUnitCost = $unitCost;

        if ($taxData) {
            $taxRate = (float)$taxData->rate;

            if ($product->tax_method == 1) {
                // Exclusive tax
                $netUnitCost = $unitCost;
                $tax = $unitCost * $qty * ($taxRate / 100);
                $cost = ($unitCost * $qty) + $tax;
            } else {
                // Inclusive tax
                $netUnitCost = (100 / (100 + $taxRate)) * $unitCost;
                $tax = ($unitCost - $netUnitCost) * $qty;
                $cost = $unitCost * $qty;
            }
        } else {
            $cost = $unitCost * $qty;
        }

        return [
            'net_unit_cost' => round($netUnitCost, 2),
            'tax_rate' => $taxRate,
            'tax' => round($tax, 2),
            'cost' => round($cost, 2),
        ];
    }

    /**
     * Create product purchase records.
     *
     * @param int $purchaseId
     * @param array<string, mixed> $purchaseData
     * @param Collection<Product> $products
     * @return void
     */
    private function createProductPurchases(int $purchaseId, array $purchaseData, $products): void
    {
        foreach ($purchaseData['product_data'] as $key => $data) {
            ProductPurchase::create([
                'purchase_id' => $purchaseId,
                'product_id' => $data['product_id'],
                'qty' => 10,
                'recieved' => 10,
                'purchase_unit_id' => $data['unit_id'],
                'net_unit_cost' => $data['net_unit_cost'],
                'discount' => 0,
                'tax_rate' => $data['tax_rate'],
                'tax' => $data['tax'],
                'total' => $data['total'],
            ]);

            // Update product quantity
            $product = $products->firstWhere('id', $data['product_id']);
            if ($product) {
                $product->increment('qty', 10);
            }
        }
    }

    /**
     * Update product warehouse stock.
     *
     * @param Collection<Product> $products
     * @param int $warehouseId
     * @return void
     */
    private function updateProductWarehouseStock($products, int $warehouseId): void
    {
        foreach ($products as $product) {
            $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($productWarehouse) {
                $productWarehouse->increment('qty', 10);
            } else {
                ProductWarehouse::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'qty' => 10,
                ]);
            }
        }
    }
}

