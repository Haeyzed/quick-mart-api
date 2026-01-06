<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Translations Table Seeder
 *
 * Seeds translations from PHP files in the translations directory.
 * Only inserts new translations that don't already exist in the database.
 *
 * @package Database\Seeders\Tenant
 */
class TranslationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $existing = $this->getExistingTranslations();
        $insertData = $this->loadTranslationsFromFiles($existing);

        if (!empty($insertData)) {
            $this->insertTranslations($insertData);
        }
    }

    /**
     * Get existing translations from database.
     *
     * @return array<string, bool> Map of existing translations (locale|key => true)
     */
    private function getExistingTranslations(): array
    {
        $existing = DB::table('translations')
            ->select('locale', 'key')
            ->get();

        $existingMap = [];
        foreach ($existing as $item) {
            $existingMap["{$item->locale}|{$item->key}"] = true;
        }

        return $existingMap;
    }

    /**
     * Load translations from PHP files.
     *
     * @param array<string, bool> $existingMap Map of existing translations
     * @return array<int, array<string, mixed>> Array of translation data to insert
     */
    private function loadTranslationsFromFiles(array $existingMap): array
    {
        $directory = database_path('seeders/translations');
        
        if (!File::exists($directory)) {
            return [];
        }

        $files = File::glob("{$directory}/*.php");
        $insertData = [];

        foreach ($files as $file) {
            $data = File::getRequire($file);

            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $row) {
                if (!isset($row['locale'], $row['key'], $row['value'])) {
                    continue;
                }

                $lookupKey = "{$row['locale']}|{$row['key']}";

                if (!isset($existingMap[$lookupKey])) {
                    $insertData[] = [
                        'locale' => $row['locale'],
                        'group' => 'db',
                        'key' => $row['key'],
                        'value' => $row['value'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $insertData;
    }

    /**
     * Insert translations in chunks.
     *
     * @param array<int, array<string, mixed>> $insertData Translation data to insert
     * @return void
     */
    private function insertTranslations(array $insertData): void
    {
        $chunks = array_chunk($insertData, 1000);

        foreach ($chunks as $chunk) {
            DB::table('translations')->insert($chunk);
        }
    }
}




