<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class PayrollsExport
 *
 * Handles the logic for exporting payroll records to Excel or PDF.
 * Supports filtering by specific IDs, date ranges, and custom column selection.
 */
class PayrollsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * PayrollsExport constructor.
     *
     * @param array<int> $ids Array of specific payroll IDs to export.
     * @param array<string> $columns Array of specific columns to include in the export.
     * @param array<string, mixed> $filters Optional filters (e.g., start_date, end_date, status).
     */
    public function __construct(
        public array $ids = [],
        public array $columns = [],
        public array $filters = []
    ) {}

    /**
     * Prepare the query for the export.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return Payroll::query()
            ->with(['employee', 'user', 'account'])
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->latest();
    }

    /**
     * Define the headings for the exported file.
     *
     * @return array<string>
     */
    public function headings(): array
    {
        return ! empty($this->columns) ? $this->columns : [
            'ID',
            'Reference No',
            'Employee Name',
            'Account',
            'Amount',
            'Month',
            'Status',
            'Paying Method',
            'Recorded By',
            'Created At',
        ];
    }

    /**
     * Map each payroll record to a row in the exported file.
     *
     * @param Payroll $row
     * @return array<mixed>
     */
    public function map($row): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $row->id;
        if (in_array('Reference No', $columns)) $mapped[] = $row->reference_no;
        if (in_array('Employee Name', $columns)) $mapped[] = $row->employee?->name;
        if (in_array('Account', $columns)) $mapped[] = $row->account?->name;
        if (in_array('Amount', $columns)) $mapped[] = $row->amount;
        if (in_array('Month', $columns)) $mapped[] = $row->month;
        if (in_array('Status', $columns)) $mapped[] = ucfirst($row->status);
        if (in_array('Paying Method', $columns)) $mapped[] = $row->paying_method;
        if (in_array('Recorded By', $columns)) $mapped[] = $row->user?->name;
        if (in_array('Created At', $columns)) $mapped[] = $row->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
