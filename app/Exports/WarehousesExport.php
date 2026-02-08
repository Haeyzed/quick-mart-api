<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Excel export for Warehouse entities.
 *
 * Exports warehouses by ID or all when ids is empty. Supports column selection.
 * Uses query-based chunking for memory efficiency.
 */
class WarehousesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Create a new WarehousesExport instance.
     *
     * @param array<int> $ids Warehouse IDs to export. Empty array exports all.
     * @param array<string> $columns Column keys to include. Empty uses defaults.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {}

    /**
     * Build the query for the export.
     *
     * @return Builder<Warehouse>
     */
    public function query(): Builder
    {
        return Warehouse::query()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
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
            'id' => 'ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'address' => 'Address',
            'is_active' => 'Status',
            'created_at' => 'Date Created',
            'updated_at' => 'Last Updated',
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
     * Map a warehouse model to an export row.
     *
     * @param Warehouse $warehouse The warehouse instance to map.
     * @return array<string|int|null> Row data matching the headings order.
     */
    public function map($warehouse): array
    {
        /** @var Warehouse $warehouse */

        $columnsToExport = $this->columns ?: [
            'id', 'name', 'phone', 'email', 'address',
            'is_active', 'created_at', 'updated_at',
        ];

        return array_map(fn ($col) => match ($col) {
            'is_active' => $warehouse->is_active ? 'Active' : 'Inactive',
            'created_at' => $warehouse->created_at?->toDateTimeString(),
            'updated_at' => $warehouse->updated_at?->toDateTimeString(),
            default => $warehouse->{$col} ?? '',
        }, $columnsToExport);
    }
}
