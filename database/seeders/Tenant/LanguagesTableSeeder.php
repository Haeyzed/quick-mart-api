<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Languages Table Seeder
 *
 * Seeds default languages for the application.
 * Sets the first language as default if no default is specified.
 *
 * @package Database\Seeders\Tenant
 */
class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (DB::table('languages')->count() > 0) {
            $this->ensureDefaultLanguage();
            return;
        }

        $this->seedDefaultLanguages();
    }

    /**
     * Ensure a default language is set.
     *
     * @return void
     */
    private function ensureDefaultLanguage(): void
    {
        $default = DB::table('languages')
            ->where('is_default', 1)
            ->first();

        if ($default) {
            return;
        }

        $first = DB::table('languages')
            ->orderBy('id')
            ->first();

        if ($first) {
            DB::table('languages')->update(['is_default' => 0]);
            DB::table('languages')
                ->where('id', $first->id)
                ->update(['is_default' => 1]);
        }
    }

    /**
     * Seed default languages.
     *
     * @return void
     */
    private function seedDefaultLanguages(): void
    {
        $languages = [
            ['id' => 1, 'language' => 'en', 'name' => 'English', 'is_default' => 1],
            ['id' => 2, 'language' => 'bn', 'name' => 'Bangla', 'is_default' => 0],
            ['id' => 3, 'language' => 'ar', 'name' => 'Arabic', 'is_default' => 0],
            ['id' => 4, 'language' => 'al', 'name' => 'Albania', 'is_default' => 0],
            ['id' => 5, 'language' => 'az', 'name' => 'Azerbaijan', 'is_default' => 0],
            ['id' => 6, 'language' => 'bg', 'name' => 'Bulgaria', 'is_default' => 0],
            ['id' => 7, 'language' => 'de', 'name' => 'Germany', 'is_default' => 0],
            ['id' => 8, 'language' => 'es', 'name' => 'Spanish', 'is_default' => 0],
            ['id' => 9, 'language' => 'fr', 'name' => 'French', 'is_default' => 0],
            ['id' => 10, 'language' => 'id', 'name' => 'Indonesian', 'is_default' => 0],
            ['id' => 11, 'language' => 'tr', 'name' => 'Turkish', 'is_default' => 0],
            ['id' => 12, 'language' => 'vi', 'name' => 'Vietnamese', 'is_default' => 0],
            ['id' => 13, 'language' => 'pt', 'name' => 'Portuguese', 'is_default' => 0],
            ['id' => 14, 'language' => 'ms', 'name' => 'Malay', 'is_default' => 0],
            ['id' => 15, 'language' => 'sr', 'name' => 'Serbian', 'is_default' => 0],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->insert(array_merge($language, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}




