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
 * Class HolidaysExport
 *
 * Handles the logic for exporting holiday records to Excel or PDF.
 * Supports filtering by specific IDs, date ranges, and custom column selection.
 */
class HolidaysExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * HolidaysExport constructor.
     *
     * @param array<int> $ids Array of specific holiday IDs to export.
     * @param array<string> $columns Array of specific columns to include in the export.
     * @param array<string, mixed> $filters Optional filters (e.g., start_date, end_date, is_approved).
     */
    public function __construct(
        public array $ids = [],
        public array $columns = [],
        public array $filters = []
    ) {}

    /**
     * Prepare the query for the export.
     * * @return Builder
     */
    public function query(): Builder
    {
        return Holiday::query()
            ->with(['user'])
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
            'Requested By',
            'From Date',
            'To Date',
            'Note',
            'Recurring',
            'Region',
            'Approved',
            'Created At',
        ];
    }

    /**
     * Map each holiday record to a row in the exported file.
     *
     * @param Holiday $row
     * @return array<mixed>
     */
    public function map($row): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $row->id;
        if (in_array('Requested By', $columns)) $mapped[] = $row->user?->name;
        if (in_array('From Date', $columns)) $mapped[] = $row->from_date?->format('Y-m-d');
        if (in_array('To Date', $columns)) $mapped[] = $row->to_date?->format('Y-m-d');
        if (in_array('Note', $columns)) $mapped[] = $row->note;
        if (in_array('Recurring', $columns)) $mapped[] = $row->recurring ? 'Yes' : 'No';
        if (in_array('Region', $columns)) $mapped[] = $row->region ?? 'N/A';
        if (in_array('Approved', $columns)) $mapped[] = $row->is_approved ? 'Yes' : 'No';
        if (in_array('Created At', $columns)) $mapped[] = $row->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
