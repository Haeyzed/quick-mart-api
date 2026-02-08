<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class CategoriesImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows
{
    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            return;
        }

        $parentId = null;
        if (!empty($data['parentcategory'])) {
            $parentName = trim((string) $data['parentcategory']);
            $parent = Category::firstOrCreate(['name' => $parentName], ['is_active' => true]);
            $parentId = $parent->id;
        }

        Category::updateOrCreate(
            ['name' => $name],
            [
                'parent_id' => $parentId,
                'short_description' => isset($data['short_description']) ? trim((string)$data['short_description']) : null,
                'is_active' => true,
            ]
        );
    }
}