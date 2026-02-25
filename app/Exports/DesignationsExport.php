<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class DesignationsExport
 *
 * Handles the logic for exporting designation records to Excel or PDF.
 * Supports filtering by specific IDs, date ranges, and custom column selection.
 */
class DesignationsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * DesignationsExport constructor.
     *
     * @param array<int> $ids Array of specific designation IDs to export.
     * @param array<string> $columns Array of specific columns to include in the export.
     * @param array<string, mixed> $filters Optional filters (e.g., start_date, end_date, is_active).
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
        return Designation::query()
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
            'Name',
            'Status',
            'Created At',
        ];
    }

    /**
     * Map each designation record to a row in the exported file.
     *
     * @param Designation $row
     * @return array<mixed>
     */
    public function map($row): array
    {
        $mapped = [];
        $columns = $this->headings();

        if (in_array('ID', $columns)) $mapped[] = $row->id;
        if (in_array('Name', $columns)) $mapped[] = $row->name;
        if (in_array('Status', $columns)) $mapped[] = $row->is_active ? 'Active' : 'Inactive';
        if (in_array('Created At', $columns)) $mapped[] = $row->created_at?->format('Y-m-d H:i:s');

        return $mapped;
    }
}
