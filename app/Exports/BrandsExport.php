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
 * Handles database-level chunked export of brands.
 */
class BrandsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param array<int> $ids
     * @param array<string> $columns
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {}

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return Brand::query()
            ->when(!empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('name');
    }

    /**
     * @return array<string>
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
     * @param mixed $brand
     * @return array<mixed>
     */
    public function map($brand): array
    {
        // Ensure $brand is typed properly for IDE, though abstract requires mixed
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