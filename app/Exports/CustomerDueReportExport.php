<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Excel export for Customer Due Report rows.
 */
class CustomerDueReportExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows  Report rows from ReportService.
     * @param  array<string>  $columns  Column keys to include.
     */
    public function __construct(
        private readonly Collection $rows,
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
        $labelMap = [
            'date' => 'Date',
            'reference_no' => 'Reference',
            'customer_name' => 'Customer Name',
            'customer_phone' => 'Customer Phone',
            'grand_total' => 'Grand Total',
            'returned_amount' => 'Returned Amount',
            'paid' => 'Paid',
            'due' => 'Due',
        ];

        $cols = $this->columns ?: array_keys($labelMap);

        return array_map(
            fn (string $col) => $labelMap[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $cols
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string|int|float|null>
     */
    public function map($row): array
    {
        $cols = $this->columns ?: [
            'date', 'reference_no', 'customer_name', 'customer_phone',
            'grand_total', 'returned_amount', 'paid', 'due',
        ];

        return array_map(fn (string $col) => $row[$col] ?? '', $cols);
    }
}
