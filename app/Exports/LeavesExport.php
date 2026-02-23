<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Leave;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeavesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        public array $ids = [],
        public array $columns = [],
        public array $filters = []
    ) {}

    public function query(): Builder
    {
        return Leave::query()
            ->with(['employee', 'leaveType', 'approver'])
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->latest();
    }

    public function headings(): array
    {
        return ! empty($this->columns) ? $this->columns : [
            'ID',
            'Employee Name',
            'Leave Type',
            'Start Date',
            'End Date',
            'Total Days',
            'Status',
            'Approver',
            'Created At',
        ];
    }

    /**
     * @param Leave $leave
     */
    public function map($leave): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $leave->id;
        if (in_array('Employee Name', $columns)) $mapped[] = $leave->employee?->name;
        if (in_array('Leave Type', $columns)) $mapped[] = $leave->leaveType?->name;
        if (in_array('Start Date', $columns)) $mapped[] = $leave->start_date?->format('Y-m-d');
        if (in_array('End Date', $columns)) $mapped[] = $leave->end_date?->format('Y-m-d');
        if (in_array('Total Days', $columns)) $mapped[] = $leave->days;
        if (in_array('Status', $columns)) $mapped[] = $leave->status;
        if (in_array('Approver', $columns)) $mapped[] = $leave->approver?->name ?? 'N/A';
        if (in_array('Created At', $columns)) $mapped[] = $leave->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
