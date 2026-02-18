<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SaleAgentsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private const DEFAULT_COLUMNS = ['id', 'name', 'email', 'phone_number', 'staff_id', 'department_id', 'designation_id', 'shift_id', 'is_active', 'created_at', 'updated_at'];

    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {
    }

    public function query(): Builder
    {
        return Employee::query()
            ->saleAgents()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->when(! empty($this->filters), fn (Builder $q) => $q->filter($this->filters))
            ->orderBy('name');
    }

    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;
        return array_map(fn (string $col) => ucfirst(str_replace('_', ' ', $col)), $columns);
    }

    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;
        return array_map(function ($col) use ($row) {
            return match ($col) {
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'created_at', 'updated_at' => $row->{$col}?->toDateTimeString(),
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }
}
