<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class EmployeesExport
 *
 * Handles the logic for exporting employee records to Excel or PDF.
 */
class EmployeesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Default columns to export if none are explicitly provided.
     */
    private const DEFAULT_COLUMNS = [
        'id',
        'staff_id',
        'name',
        'email',
        'phone_number',
        'department',
        'designation',
        'basic_salary',
        'is_sale_agent',
        'is_active',
        'created_at',
    ];

    /**
     * EmployeesExport constructor.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {}

    /**
     * Prepare the query for the export.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return Employee::query()
            ->with(['department', 'designation', 'shift'])
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->orderBy('name');
    }

    /**
     * Define the headings for the exported file.
     *
     * @return array<string>
     */
    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(
            fn(string $col) => ucwords(str_replace('_', ' ', $col)),
            $columns
        );
    }

    /**
     * Map each employee record to a row in the exported file.
     *
     * @param Employee $row
     * @return array<mixed>
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'id' => $row->id,
                'staff_id' => $row->staff_id,
                'name' => $row->name,
                'email' => $row->email ?? 'N/A',
                'phone_number' => $row->phone_number ?? 'N/A',
                'department' => $row->department?->name ?? 'N/A',
                'designation' => $row->designation?->name ?? 'N/A',
                'shift' => $row->shift?->name ?? 'N/A',
                'basic_salary' => $row->basic_salary,
                'is_sale_agent' => $row->is_sale_agent ? 'Yes' : 'No',
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'created_at' => $row->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at?->format('Y-m-d H:i:s'),
                default => $row->{$col} ?? 'N/A',
            };
        }, $columns);
    }
}
