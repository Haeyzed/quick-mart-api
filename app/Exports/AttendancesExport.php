<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class AttendancesExport
 *
 * Handles the logic for exporting attendance records to Excel or PDF.
 * Supports filtering by specific IDs, date ranges, and custom column selection.
 */
class AttendancesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * AttendancesExport constructor.
     *
     * @param array<int> $ids Array of specific attendance IDs to export.
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
        return Attendance::query()
            ->with(['employee', 'user'])
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->latest('date');
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
            'Date',
            'Employee Name',
            'Recorded By',
            'Check-In',
            'Check-Out',
            'Status',
            'Note',
            'Created At',
        ];
    }

    /**
     * Map each attendance record to a row in the exported file.
     *
     * @param Attendance $row
     * @return array<mixed>
     */
    public function map($row): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $row->id;
        if (in_array('Date', $columns)) $mapped[] = $row->date?->format('Y-m-d');
        if (in_array('Employee Name', $columns)) $mapped[] = $row->employee?->name;
        if (in_array('Recorded By', $columns)) $mapped[] = $row->user?->name;
        if (in_array('Check-In', $columns)) $mapped[] = $row->checkin;
        if (in_array('Check-Out', $columns)) $mapped[] = $row->checkout;
        if (in_array('Status', $columns)) $mapped[] = $row->status ? 'Active' : 'Inactive';
        if (in_array('Note', $columns)) $mapped[] = $row->note;
        if (in_array('Created At', $columns)) $mapped[] = $row->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
