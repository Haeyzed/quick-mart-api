<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class BrandsExport
 *
 * Handles memory-efficient exporting of Brands using database-level chunking.
 * Supports dynamic column selection and standardized date formatting.
 */
class BrandsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * BrandsExport constructor.
     *
     * @param array<int> $ids Specific Brand IDs to export. If empty, all brands are exported.
     * @param array<string> $columns List of specific columns to include in the export.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {
    }

    /**
     * Prepare the query for the export.
     * Using FromQuery instead of FromCollection prevents memory exhaustion for large datasets.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return Brand::query()
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('name');
    }

    /**
     * Define the headings for the Excel file based on selected columns.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        $allLabels = [
            'id'                => 'ID',
            'name'              => 'Brand Name',
            'slug'              => 'URL Slug',
            'short_description' => 'Description',
            'page_title'        => 'SEO Title',
            'image_url'         => 'Image Source',
            'is_active'         => 'Status',
            'created_at'        => 'Date Created',
            'updated_at'        => 'Last Updated',
        ];

        if (empty($this->columns)) {
            return array_values($allLabels);
        }

        return array_map(
            fn($col) => $allLabels[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    /**
     * Map each Brand model instance to a row in the export.
     *
     * @param Brand $brand
     * @return array<int, mixed>
     */
    public function map($brand): array
    {
        $columnsToExport = $this->columns ?: [
            'id', 'name', 'slug', 'short_description', 'page_title', 'image_url', 'is_active', 'created_at', 'updated_at'
        ];

        $row = [];
        foreach ($columnsToExport as $column) {
            $row[] = match ($column) {
                'is_active'  => $brand->is_active ? 'Active' : 'Inactive',
                'created_at' => $brand->created_at?->toDateTimeString(),
                'updated_at' => $brand->updated_at?->toDateTimeString(),
                default      => $brand->{$column} ?? '',
            };
        }

        return $row;
    }
}