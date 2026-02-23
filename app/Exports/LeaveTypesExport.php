<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveTypesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        public array $ids = [],
        public array $columns = [],
        public array $filters = []
    ) {}

    public function query(): Builder
    {
        return LeaveType::query()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->latest();
    }

    public function headings(): array
    {
        return ! empty($this->columns) ? $this->columns : [
            'ID',
            'Name',
            'Annual Quota',
            'Encashable',
            'Carry Forward Limit',
            'Status',
            'Created At',
        ];
    }

    /**
     * @param LeaveType $leaveType
     */
    public function map($leaveType): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $leaveType->id;
        if (in_array('Name', $columns)) $mapped[] = $leaveType->name;
        if (in_array('Annual Quota', $columns)) $mapped[] = $leaveType->annual_quota;
        if (in_array('Encashable', $columns)) $mapped[] = $leaveType->encashable ? 'Yes' : 'No';
        if (in_array('Carry Forward Limit', $columns)) $mapped[] = $leaveType->carry_forward_limit;
        if (in_array('Status', $columns)) $mapped[] = $leaveType->is_active ? 'Active' : 'Inactive';
        if (in_array('Created At', $columns)) $mapped[] = $leaveType->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
