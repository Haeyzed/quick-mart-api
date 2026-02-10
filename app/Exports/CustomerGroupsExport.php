<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Excel export for Customer Group entities.
 *
 * Exports customer groups by ID or all when ids is empty. Supports column selection.
 */
class CustomerGroupsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param  array<int>  $ids  Customer group IDs to export. Empty array exports all.
     * @param  array<string>  $columns  Column keys to include. Empty uses defaults.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {}

    public function query(): Builder
    {
        return CustomerGroup::query()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('name');
    }

    public function headings(): array
    {
        $labelMap = [
            'id' => 'ID',
            'name' => 'Name',
            'percentage' => 'Percentage',
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
     * @param  CustomerGroup  $customerGroup
     * @return array<string|int|null>
     */
    public function map($customerGroup): array
    {
        $columnsToExport = $this->columns ?: ['id', 'name', 'percentage', 'is_active', 'created_at', 'updated_at'];

        return array_map(fn ($col) => match ($col) {
            'is_active' => $customerGroup->is_active ? 'Active' : 'Inactive',
            'created_at' => $customerGroup->created_at?->toDateTimeString(),
            'updated_at' => $customerGroup->updated_at?->toDateTimeString(),
            default => $customerGroup->{$col} ?? '',
        }, $columnsToExport);
    }
}
