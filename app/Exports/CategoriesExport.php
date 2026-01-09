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
        private readonly array $ids = []
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
        return [
            'name',
            'parent_category',
        ];
    }

    /**
     * Map each category to a row in the Excel file.
     *
     * @param Category $category
     * @return array
     */
    public function map($category): array
    {
        return [
            $category->name,
            $category->parent?->name ?? '',
        ];
    }
}

