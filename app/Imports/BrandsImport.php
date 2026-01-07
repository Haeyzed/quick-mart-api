<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * BrandsImport
 *
 * Handles importing brands from CSV/Excel files.
 */
class BrandsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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

            $name = trim($row['name'] ?? '');
            $shortDescription = trim($row['short_description'] ?? '');
            $imageUrl = trim($row['image_url'] ?? '');
            $pageTitle = trim($row['page_title'] ?? '');

            // Find or create brand
            $brand = Brand::firstOrNew(
                ['name' => $name, 'is_active' => true]
            );

            $brand->name = $name;
            $brand->image_url = $imageUrl ?: null;
            $brand->page_title = $pageTitle ?: null;
            $brand->short_description = $shortDescription;
            $brand->is_active = true;

            // Generate slug if not set
            if (!$brand->slug && $brand->name) {
                $brand->slug = Brand::generateUniqueSlug($brand->name);
            }

            $brand->save();
        }
    }
}
