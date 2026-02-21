<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CurrenciesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private const DEFAULT_COLUMNS = [
        'id',
        'name',
        'code',
        'symbol',
        'country_id',
        'precision',
        'symbol_native',
        'symbol_first',
        'decimal_mark',
        'thousands_separator',
    ];

    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {}

    public function query(): Builder
    {
        return Currency::query()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->orderBy('name');
    }

    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(
            fn (string $col) => ucfirst(str_replace('_', ' ', $col)),
            $columns
        );
    }

    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'country_id' => $row->country?->name ?? $row->country_id,
                'symbol_first' => $row->symbol_first ? 'Yes' : 'No',
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }
}
