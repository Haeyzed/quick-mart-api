<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export holidays to Excel/PDF.
 */
class HolidaysExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private const DEFAULT_COLUMNS = [
        'id',
        'user_id',
        'from_date',
        'to_date',
        'note',
        'is_approved',
        'recurring',
        'region',
        'created_at',
        'updated_at',
    ];

    /** @param array<int> $ids @param array<string> $columns @param array<string, mixed> $filters */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {
    }

    public function query(): Builder
    {
        return Holiday::query()
            ->with('user')
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->when(! empty($this->filters), fn (Builder $q) => $q->filter($this->filters))
            ->latest('from_date');
    }

    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;
        return array_map(fn (string $col) => ucfirst(str_replace('_', ' ', $col)), $columns);
    }

    /** @param Holiday $row @return array<int, mixed> */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;
        return array_map(function ($col) use ($row) {
            return match ($col) {
                'from_date', 'to_date' => $row->{$col}?->format('Y-m-d'),
                'is_approved' => $row->is_approved ? 'Yes' : 'No',
                'recurring' => $row->recurring ? 'Yes' : 'No',
                'created_at', 'updated_at' => $row->{$col}?->toDateTimeString(),
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }
}
