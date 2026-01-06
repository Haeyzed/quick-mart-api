<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DsoAlert as DsoAlertModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DSO Alert Command
 *
 * Finds all products that could not fulfill their daily sale objective (DSO)
 * for the previous day. This command should be scheduled to run daily
 * to track product performance against sales targets.
 *
 * @package App\Console\Commands
 */
class DsoAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dsoalert:find';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find all products that could not fulfill daily sale objective';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function handle(): int
    {
        try {
            $this->info('Checking daily sale objectives...');

            $date = Carbon::yesterday()->format('Y-m-d');
            $products = $this->getProductsBelowDso($date);

            if ($products->isEmpty()) {
                $this->info('All products met their daily sale objectives.');
                return Command::SUCCESS;
            }

            $this->info("Found {$products->count()} products below DSO.");

            $this->createDsoAlert($products, $date);

            $this->info('DSO alert created successfully.');
            return Command::SUCCESS;
        } catch (Exception $e) {
            Log::error('DsoAlert: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Get products that did not meet their daily sale objective.
     *
     * @param string $date Date to check (Y-m-d format)
     * @return Collection Collection of products below DSO
     */
    private function getProductsBelowDso(string $date)
    {
        // Temporarily disable strict mode for complex query
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        try {
            $products = DB::table('products')
                ->leftJoin('product_sales', function ($join) use ($date) {
                    $join->on('products.id', '=', 'product_sales.product_id')
                        ->whereDate('product_sales.created_at', $date)
                        ->whereNotNull('products.daily_sale_objective');
                })
                ->whereNotNull('products.daily_sale_objective')
                ->where('products.daily_sale_objective', '>', function ($query) use ($date) {
                    $query->select(DB::raw('COALESCE(SUM(product_sales.qty), 0)'))
                        ->from('product_sales')
                        ->whereColumn('product_sales.product_id', 'products.id')
                        ->whereDate('product_sales.created_at', $date);
                })
                ->select('products.name', 'products.code')
                ->groupBy('products.code', 'products.name')
                ->get();
        } finally {
            // Re-enable strict mode
            config()->set('database.connections.mysql.strict', true);
            DB::reconnect();
        }

        return $products;
    }

    /**
     * Create DSO alert record in database.
     *
     * @param Collection $products Products below DSO
     * @param string $date Date of the alert
     * @return void
     */
    private function createDsoAlert($products, string $date): void
    {
        DsoAlertModel::create([
            'product_info' => json_encode($products),
            'number_of_products' => $products->count(),
            'date' => $date,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}

