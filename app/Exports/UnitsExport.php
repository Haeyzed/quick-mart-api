<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Excel export for Unit entities.
 *
 * Exports units by ID or all when ids is empty. Supports column selection.
 * Uses query-based chunking for memory efficiency.
 */
class UnitsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Create a new UnitsExport instance.
     *
     * @param array<int> $ids Unit IDs to export. Empty array exports all.
     * @param array<string> $columns Column keys to include. Empty uses defaults.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {}

    /**
     * Build the query for the export.
     *
     * @return Builder<Unit>
     */
    public function query(): Builder
    {
        return Unit::query()
            ->with('baseUnitRelation:id,code,name')
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('code');
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
            'code' => 'Code',
            'name' => 'Name',
            'base_unit_name' => 'Base Unit',
            'operator' => 'Operator',
            'operation_value' => 'Operation Value',
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
     * Map a unit model to an export row.
     *
     * @param Unit $unit The unit instance to map.
     * @return array<string|int|float|null> Row data matching the headings order.
     */
    public function map($unit): array
    {
        /** @var Unit $unit */

        $columnsToExport = $this->columns ?: [
            'id', 'code', 'name', 'base_unit_name', 'operator', 'operation_value',
            'is_active', 'created_at', 'updated_at',
        ];

        return array_map(fn ($col) => match ($col) {
            'base_unit_name' => $unit->baseUnitRelation?->name ?? '',
            'is_active' => $unit->is_active ? 'Active' : 'Inactive',
            'created_at' => $unit->created_at?->toDateTimeString(),
            'updated_at' => $unit->updated_at?->toDateTimeString(),
            default => $unit->{$col} ?? '',
        }, $columnsToExport);
    }
}
