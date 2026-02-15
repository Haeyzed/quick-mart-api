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
 * */
class UnitsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     *
     */
    private const DEFAULT_COLUMNS = [
        'id',
        'code',
        'name',
        'base_unit_name',
        'operator',
        'operation_value',
        'is_active',
        'created_at',
        'updated_at',
    ];

    /**
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string> $filters
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    )
    {
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return Unit::query()
            ->with('baseUnitRelation:id,code,name')
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->orderBy('code');
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
     * @param Unit $row
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'base_unit_name' => $row->baseUnitRelation?->name ?? 'Base',
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'created_at' => $row->created_at?->toDateTimeString(),
                'updated_at' => $row->updated_at?->toDateTimeString(),
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }
}
