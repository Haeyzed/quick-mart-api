<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Traits\CacheForget;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Reset Database Command
 *
 * Resets the database by dropping all tables and optionally importing a seed SQL file.
 * This command is useful for development, testing, or demo environments.
 *
 * WARNING: This command will destroy all data in the database!
 *
 * @package App\Console\Commands
 */
class ResetDB extends Command
{
    use CacheForget;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset
                            {--seed-file= : Path to SQL seed file to import after reset}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the database by dropping all tables and optionally importing seed data';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('This will destroy all data in the database. Continue?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            $this->info('Clearing cache...');
            $this->clearAllCache();

            $this->info('Dropping all tables...');
            $this->dropAllTables();

            $seedFile = $this->option('seed-file');
            if ($seedFile) {
                $this->info("Importing seed file: {$seedFile}");
                $this->importSeedFile($seedFile);
            } else {
                $this->info('Running migrations...');
                Artisan::call('migrate', ['--force' => true]);
            }

            $this->info('Database reset completed successfully.');
            return Command::SUCCESS;
        } catch (Exception $e) {
            Log::error('ResetDB: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Clear all application cache entries.
     *
     * @return void
     */
    private function clearAllCache(): void
    {
        $cacheKeys = [
            'biller_list',
            'brand_list',
            'category_list',
            'coupon_list',
            'customer_list',
            'customer_group_list',
            'product_list',
            'product_list_with_variant',
            'warehouse_list',
            'table_list',
            'tax_list',
            'currency',
            'general_setting',
            'pos_setting',
            'user_role',
            'permissions',
            'role_has_permissions',
            'role_has_permissions_list',
        ];

        foreach ($cacheKeys as $key) {
            $this->cacheForget($key);
        }
    }

    /**
     * Drop all tables in the database.
     *
     * @return void
     */
    private function dropAllTables(): void
    {
        // Disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        try {
            $tables = DB::select('SHOW TABLES');
            $databaseName = config('database.connections.mysql.database');
            $key = "Tables_in_{$databaseName}";

            foreach ($tables as $table) {
                $tableName = $table->$key;
                Schema::dropIfExists($tableName);
                $this->line("Dropped table: {$tableName}");
            }
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        }
    }

    /**
     * Import a seed SQL file into the database.
     *
     * @param string $seedFile Path to the SQL file
     * @return void
     * @throws Exception If file doesn't exist or import fails
     */
    private function importSeedFile(string $seedFile): void
    {
        $fullPath = base_path($seedFile);

        if (!File::exists($fullPath)) {
            throw new Exception("Seed file not found: {$fullPath}");
        }

        $sql = File::get($fullPath);

        if (empty($sql)) {
            throw new Exception("Seed file is empty: {$fullPath}");
        }

        DB::unprepared($sql);
        $this->info("Successfully imported seed file.");
    }
}

