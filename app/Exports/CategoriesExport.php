<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * CategoriesExport
 *
 * Handles exporting categories to Excel.
 */
class CategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {
    }

    /**
     * Get the collection of categories to export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Category::query()->with('parent:id,name');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Define the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        $columnLabels = [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'short_description' => 'Short Description',
            'parent_name' => 'Parent Category',
            'featured' => 'Featured',
            'is_active' => 'Is Active',
            'is_sync_disable' => 'Is Sync Disabled',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        if (empty($this->columns)) {
            return array_values($columnLabels);
        }

        return array_map(fn($col) => $columnLabels[$col] ?? ucfirst(str_replace('_', ' ', $col)), $this->columns);
    }

    /**
     * Map each category to a row in the Excel file.
     *
     * @param Category $category
     * @return array
     */
    public function map($category): array
    {
        $defaultColumns = ['id', 'name', 'slug', 'short_description', 'parent_name', 'featured', 'is_active', 'is_sync_disable', 'created_at', 'updated_at'];
        $columnsToExport = empty($this->columns) ? $defaultColumns : $this->columns;

        $data = [];
        foreach ($columnsToExport as $column) {
            match ($column) {
                'id' => $data[] = $category->id,
                'name' => $data[] = $category->name,
                'slug' => $data[] = $category->slug ?? '',
                'short_description' => $data[] = $category->short_description ?? '',
                'parent_name' => $data[] = $category->parent?->name ?? '',
                'featured' => $data[] = $category->featured ? 'Yes' : 'No',
                'is_active' => $data[] = $category->is_active ? 'Yes' : 'No',
                'is_sync_disable' => $data[] = $category->is_sync_disable ? 'Yes' : 'No',
                'created_at' => $data[] = $category->created_at?->format('Y-m-d H:i:s') ?? '',
                'updated_at' => $data[] = $category->updated_at?->format('Y-m-d H:i:s') ?? '',
                default => $data[] = '',
            };
        }

        return $data;
    }
}

