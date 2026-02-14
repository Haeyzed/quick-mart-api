<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Category;
use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Excel export for Category entities.
 *
 * Exports categories by ID or all when ids is empty. Supports column selection.
 * Uses query-based chunking for memory efficiency. Includes parent relationship.
 */
class CategoriesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable, FilterableByDates;


    /**
     *
     */
    private const DEFAULT_COLUMNS = [
        'id',
        'name',
        'slug',
        'short_description',
        'parent_name',
        'is_active',
        'featured',
        'is_sync_disable',
        'created_at',
    ];

    /**
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string> $filters
     */
    public function __construct(
        private readonly array   $ids = [],
        private readonly array   $columns = [],
        private readonly array   $filters = [],
    )
    {
    }

    /**
     * Build the query for the export.
     *
     * @return Builder<Category>
     */
    public function query(): Builder
    {
        return Category::query()
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->orderBy('name');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(
            fn(string $col) => ucfirst(str_replace('_', ' ', $col)),
            $columns
        );
    }

    /**
     * @param Category $row
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'featured' => $row->featured ? 'Yes' : 'No',
                'is_sync_disable' => $row->is_sync_disable ? 'Disabled' : 'Enabled',
                'created_at' => $row->created_at?->toDateTimeString(),
                'updated_at' => $row->updated_at?->toDateTimeString(),
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }
}
