<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * CategoriesImport
 *
 * Handles importing categories from CSV/Excel files.
 */
class CategoriesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
            $parentCategoryName = trim($row['parentcategory'] ?? '');

            // Find or create parent category if provided
            $parentId = null;
            if (!empty($parentCategoryName)) {
                $parentCategory = Category::firstOrNew(
                    ['name' => $parentCategoryName, 'is_active' => true]
                );
                if (!$parentCategory->exists) {
                    $parentCategory->is_active = true;
                    $parentCategory->save();
                }
                $parentId = $parentCategory->id;
            }

            // Find or create category
            $category = Category::firstOrNew(
                ['name' => $name, 'is_active' => true]
            );

            $category->parent_id = $parentId;
            $category->is_active = true;

            // Generate slug if not set
            if (!$category->slug) {
                $category->slug = Category::generateUniqueSlug($name);
            }

            $category->save();
        }
    }
}
