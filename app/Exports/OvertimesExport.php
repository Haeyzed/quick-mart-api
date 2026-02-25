<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Overtime;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class OvertimesExport
 *
 * Handles the logic for exporting overtime records to Excel or PDF.
 * Supports filtering by specific IDs, date ranges, and custom column selection.
 */
class OvertimesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * OvertimesExport constructor.
     *
     * @param array<int> $ids Array of specific overtime IDs to export.
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
     */
    public function query(): Builder
    {
        return Overtime::query()
            ->with(['employee', 'approver'])
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
            'Employee Name',
            'Date',
            'Hours',
            'Amount',
            'Status',
            'Approver',
            'Created At',
        ];
    }

    /**
     * Map each overtime record to a row in the exported file.
     *
     * @param Overtime $row
     * @return array<mixed>
     */
    public function map($row): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $row->id;
        if (in_array('Employee Name', $columns)) $mapped[] = $row->employee?->name;
        if (in_array('Date', $columns)) $mapped[] = $row->date?->format('Y-m-d');
        if (in_array('Hours', $columns)) $mapped[] = $row->hours;
        if (in_array('Amount', $columns)) $mapped[] = $row->amount;
        if (in_array('Status', $columns)) $mapped[] = $row->status;
        if (in_array('Approver', $columns)) $mapped[] = $row->approver?->name ?? 'N/A';
        if (in_array('Created At', $columns)) $mapped[] = $row->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
