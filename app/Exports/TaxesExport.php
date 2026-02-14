<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Excel export for Tax entities.
 *
 * Exports taxes by ID or all when ids is empty. Supports column selection.
 * Uses query-based chunking for memory efficiency.
 */
class TaxesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Create a new TaxesExport instance.
     *
     * @param array<int> $ids Tax IDs to export. Empty array exports all.
     * @param array<string> $columns Column keys to include. Empty uses defaults.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    )
    {
    }

    /**
     * Build the query for the export.
     *
     * @return Builder<Tax>
     */
    public function query(): Builder
    {
        return Tax::query()
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
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
            'rate' => 'Rate (%)',
            'is_active' => 'Status',
            'woocommerce_tax_id' => 'WooCommerce ID',
            'created_at' => 'Date Created',
            'updated_at' => 'Last Updated',
        ];

        if (empty($this->columns)) {
            return array_values($labelMap);
        }

        return array_map(
            fn($col) => $labelMap[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    /**
     * Map a tax model to an export row.
     *
     * @param Tax $tax The tax instance to map.
     * @return array<string|int|float|null> Row data matching the headings order.
     */
    public function map($tax): array
    {
        /** @var Tax $tax */

        $columnsToExport = $this->columns ?: [
            'id', 'name', 'rate', 'is_active', 'woocommerce_tax_id',
            'created_at', 'updated_at',
        ];

        return array_map(fn($col) => match ($col) {
            'is_active' => $tax->is_active ? 'Active' : 'Inactive',
            'created_at' => $tax->created_at?->toDateTimeString(),
            'updated_at' => $tax->updated_at?->toDateTimeString(),
            default => $tax->{$col} ?? '',
        }, $columnsToExport);
    }
}

