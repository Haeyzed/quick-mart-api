<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Income;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IncomesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private const DEFAULT_COLUMNS = ['id', 'reference_no', 'income_category_id', 'warehouse_id', 'account_id', 'user_id', 'amount', 'note', 'created_at', 'updated_at'];

    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {
    }

    public function query(): Builder
    {
        return Income::query()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->when(! empty($this->filters), fn (Builder $q) => $q->filter($this->filters))
            ->latest('created_at');
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
            return in_array($col, ['created_at', 'updated_at'], true) ? $row->{$col}?->toDateTimeString() : ($row->{$col} ?? '');
        }, $columns);
    }
}
