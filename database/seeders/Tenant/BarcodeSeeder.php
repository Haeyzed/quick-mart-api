<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Barcode Seeder
 *
 * Seeds default barcode label configurations for printing.
 * These configurations define various label sizes and layouts.
 *
 * @package Database\Seeders\Tenant
 */
class BarcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (DB::table('barcodes')->count() > 0) {
            return;
        }

        $barcodes = [
            [
                'name' => '20 Labels per Sheet',
                'description' => 'Sheet Size: 8.5" x 11", Label Size: 4" x 1", Labels per sheet: 20',
                'width' => 4.0000,
                'height' => 1.0000,
                'paper_width' => 8.5000,
                'paper_height' => 11.0000,
                'top_margin' => 0.5000,
                'left_margin' => 0.1250,
                'row_distance' => 0.0000,
                'col_distance' => 0.1875,
                'stickers_in_one_row' => 2,
                'is_default' => 0,
                'is_continuous' => 0,
                'stickers_in_one_sheet' => 20,
                'is_custom' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 Labels per sheet',
                'description' => 'Sheet Size: 8.5" x 11", Label Size: 2.625" x 1", Labels per sheet: 30',
                'width' => 2.6250,
                'height' => 1.0000,
                'paper_width' => 8.5000,
                'paper_height' => 11.0000,
                'top_margin' => 0.5000,
                'left_margin' => 0.1880,
                'row_distance' => 0.0000,
                'col_distance' => 0.1250,
                'stickers_in_one_row' => 3,
                'is_default' => 0,
                'is_continuous' => 0,
                'stickers_in_one_sheet' => 30,
                'is_custom' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '32 Labels per sheet',
                'description' => 'Sheet Size: 8.5" x 11", Label Size: 2" x 1.25", Labels per sheet: 32',
                'width' => 2.0000,
                'height' => 1.2500,
                'paper_width' => 8.5000,
                'paper_height' => 11.0000,
                'top_margin' => 0.5000,
                'left_margin' => 0.2500,
                'row_distance' => 0.0000,
                'col_distance' => 0.0000,
                'stickers_in_one_row' => 4,
                'is_default' => 0,
                'is_continuous' => 0,
                'stickers_in_one_sheet' => 32,
                'is_custom' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '40 Labels per sheet',
                'description' => 'Sheet Size: 8.5" x 11", Label Size: 2" x 1", Labels per sheet: 40',
                'width' => 2.0000,
                'height' => 1.0000,
                'paper_width' => 8.5000,
                'paper_height' => 11.0000,
                'top_margin' => 0.5000,
                'left_margin' => 0.2500,
                'row_distance' => 0.0000,
                'col_distance' => 0.0000,
                'stickers_in_one_row' => 4,
                'is_default' => 0,
                'is_continuous' => 0,
                'stickers_in_one_sheet' => 40,
                'is_custom' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '50 Labels per Sheet',
                'description' => 'Sheet Size: 8.5" x 11", Label Size: 1.5" x 1", Labels per sheet: 50',
                'width' => 1.5000,
                'height' => 1.0000,
                'paper_width' => 8.5000,
                'paper_height' => 11.0000,
                'top_margin' => 0.5000,
                'left_margin' => 0.5000,
                'row_distance' => 0.0000,
                'col_distance' => 0.0000,
                'stickers_in_one_row' => 5,
                'is_default' => 0,
                'is_continuous' => 0,
                'stickers_in_one_sheet' => 50,
                'is_custom' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Continuous Rolls - 31.75mm x 25.4mm',
                'description' => 'Label Size: 31.75mm x 25.4mm, Gap: 3.18mm',
                'width' => 1.2500,
                'height' => 1.0000,
                'paper_width' => 1.2500,
                'paper_height' => 0.0000,
                'top_margin' => 0.1250,
                'left_margin' => 0.0000,
                'row_distance' => 0.1250,
                'col_distance' => 0.0000,
                'stickers_in_one_row' => 1,
                'is_default' => 0,
                'is_continuous' => 1,
                'stickers_in_one_sheet' => null,
                'is_custom' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('barcodes')->insert($barcodes);
    }
}




