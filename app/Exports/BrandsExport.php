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
 * Excel export for Brand entities.
 *
 * Exports brands by ID or all when ids is empty. Supports column selection.
 * Uses query-based chunking for memory efficiency.
 */
class BrandsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Create a new BrandsExport instance.
     *
     * @param array<int> $ids Brand IDs to export. Empty array exports all.
     * @param array<string> $columns Column keys to include. Empty uses defaults.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {}

    /**
     * Build the query for the export.
     *
     * @return Builder<Brand>
     */
    public function query(): Builder
    {
        return Brand::query()
            ->when(!empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('name');
    }

    /**
     * Get the column headings for the export.
     *
     * @return array<string> Column header labels.
     */
    public function headings(): array
    {
        $labelMap = [
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
            return array_values($labelMap);
        }

        return array_map(
            fn ($col) => $labelMap[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    /**
     * Map a brand model to an export row.
     *
     * @param Brand $brand The brand instance to map.
     * @return array<string|int|null> Row data matching the headings order.
     */
    public function map($brand): array
    {
        /** @var Brand $brand */

        $columnsToExport = $this->columns ?: [
            'id', 'name', 'slug', 'short_description', 'page_title', 
            'image_url', 'is_active', 'created_at', 'updated_at'
        ];

        return array_map(fn ($col) => match ($col) {
            'is_active'  => $brand->is_active ? 'Active' : 'Inactive',
            'created_at' => $brand->created_at?->toDateTimeString(),
            'updated_at' => $brand->updated_at?->toDateTimeString(),
            default      => $brand->{$col} ?? '',
        }, $columnsToExport);
    }
}