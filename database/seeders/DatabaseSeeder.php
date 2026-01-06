<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Tenant\TenantDatabaseSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database Seeder
 *
 * Main seeder that calls all tenant seeders and conditionally
 * seeds module-specific data based on enabled modules.
 *
 * @package Database\Seeders
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(TenantDatabaseSeeder::class);

        // Seed module-specific data if modules are enabled
        if (Schema::hasTable('general_settings')) {
            $generalSetting = DB::table('general_settings')
                ->select('modules')
                ->first();

            if ($generalSetting && !empty($generalSetting->modules)) {
                $modules = explode(',', $generalSetting->modules);

                if (in_array('restaurant', $modules, true)) {
                    $this->seedRestaurantModules();
                }
            }
        }
    }

    /**
     * Seed restaurant module data.
     *
     * @return void
     */
    private function seedRestaurantModules(): void
    {
        if (class_exists(\Modules\Restaurant\Database\Seeders\RestaurantDatabaseSeeder::class)) {
            $this->call(\Modules\Restaurant\Database\Seeders\RestaurantDatabaseSeeder::class);
        }

        if (class_exists(\Modules\Restaurant\Database\Seeders\RestaurantProductSeeder::class)) {
            $this->call(\Modules\Restaurant\Database\Seeders\RestaurantProductSeeder::class);
        }
    }
}
