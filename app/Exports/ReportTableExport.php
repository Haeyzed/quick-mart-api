<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Generic Excel export for report table rows.
 */
class ReportTableExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<string, string>  $columnLabels
     * @param  array<string>  $columns
     */
    public function __construct(
        private readonly Collection $rows,
        private readonly array $columnLabels,
        private readonly array $columns = []
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    /**
     * @return array<string>
     */
    public function headings(): array
    {
        $cols = $this->columns ?: array_keys($this->columnLabels);

        return array_map(
            fn (string $col) => $this->columnLabels[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $cols
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string|int|float|null>
     */
    public function map($row): array
    {
        $cols = $this->columns ?: array_keys($this->columnLabels);

        return array_map(fn (string $col) => $row[$col] ?? '', $cols);
    }
}
