<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {}

    public function query(): Builder
    {
        return Category::query()
            ->with('parent:id,name')
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('name');
    }

    public function headings(): array
    {
        $allLabels = [
            'id'                => 'ID',
            'name'              => 'Name',
            'slug'              => 'Slug',
            'short_description' => 'Short Description',
            'parent_name'       => 'Parent Category',
            'featured'          => 'Featured',
            'is_active'         => 'Is Active',
            'is_sync_disable'   => 'Is Sync Disabled',
            'created_at'        => 'Created At',
            'updated_at'        => 'Updated At',
        ];

        if (empty($this->columns)) {
            return array_values($allLabels);
        }

        return array_map(fn($col) => $allLabels[$col] ?? ucfirst(str_replace('_', ' ', $col)), $this->columns);
    }

    public function map($category): array
    {
        /** @var Category $category */
        $columnsToExport = $this->columns ?: [
            'id', 'name', 'slug', 'short_description', 'parent_name', 
            'featured', 'is_active', 'is_sync_disable', 'created_at', 'updated_at'
        ];

        return array_map(fn($col) => match ($col) {
            'parent_name'     => $category->parent?->name ?? '',
            'featured'        => $category->featured ? 'Yes' : 'No',
            'is_active'       => $category->is_active ? 'Yes' : 'No',
            'is_sync_disable' => $category->is_sync_disable ? 'Yes' : 'No',
            'created_at'      => $category->created_at?->toDateTimeString(),
            'updated_at'      => $category->updated_at?->toDateTimeString(),
            default           => $category->{$col} ?? '',
        }, $columnsToExport);
    }
}