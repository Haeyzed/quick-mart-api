<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CountriesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private const DEFAULT_COLUMNS = [
        'id',
        'iso2',
        'name',
        'phone_code',
        'iso3',
        'region',
        'subregion',
        'status',
    ];

    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {}

    public function query(): Builder
    {
        return Country::query()
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
                'status' => $row->status ? 'Active' : 'Inactive',
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }
}
