<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ShiftsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        public array $ids = [],
        public array $columns = [],
        public array $filters = []
    ) {}

    public function query(): Builder
    {
        return Shift::query()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->latest();
    }

    public function headings(): array
    {
        return ! empty($this->columns) ? $this->columns : [
            'ID',
            'Name',
            'Start Time',
            'End Time',
            'Late Time Limit',
            'Status',
            'Created At',
        ];
    }

    /**
     * @param Shift $row
     */
    public function map($row): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $row->id;
        if (in_array('Name', $columns)) $mapped[] = $row->name;
        if (in_array('Start Time', $columns)) $mapped[] = $row->start_time;
        if (in_array('End Time', $columns)) $mapped[] = $row->end_time;
        if (in_array('Late Time Limit', $columns)) $mapped[] = $row->late_time_limit;
        if (in_array('Status', $columns)) $mapped[] = $row->is_active ? 'Active' : 'Inactive';
        if (in_array('Created At', $columns)) $mapped[] = $row->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
