<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * BrandsExport
 *
 * Handles exporting brands to Excel.
 */
class BrandsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {
    }

    /**
     * Get the collection of brands to export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Brand::query();

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
            'page_title' => 'Page Title',
            'image_url' => 'Image URL',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        if (empty($this->columns)) {
            return array_values($columnLabels);
        }

        return array_map(fn($col) => $columnLabels[$col] ?? ucfirst(str_replace('_', ' ', $col)), $this->columns);
    }

    /**
     * Map each brand to a row in the Excel file.
     *
     * @param Brand $brand
     * @return array
     */
    public function map($brand): array
    {
        $defaultColumns = ['id', 'name', 'slug', 'short_description', 'page_title', 'image_url', 'is_active', 'created_at', 'updated_at'];
        $columnsToExport = empty($this->columns) ? $defaultColumns : $this->columns;

        $data = [];
        foreach ($columnsToExport as $column) {
            match ($column) {
                'id' => $data[] = $brand->id,
                'name' => $data[] = $brand->name,
                'slug' => $data[] = $brand->slug ?? '',
                'short_description' => $data[] = $brand->short_description ?? '',
                'page_title' => $data[] = $brand->page_title ?? '',
                'image_url' => $data[] = $brand->image_url ?? '',
                'is_active' => $data[] = $brand->is_active ? 'Yes' : 'No',
                'created_at' => $data[] = $brand->created_at?->format('Y-m-d H:i:s') ?? '',
                'updated_at' => $data[] = $brand->updated_at?->format('Y-m-d H:i:s') ?? '',
                default => $data[] = '',
            };
        }

        return $data;
    }
}

