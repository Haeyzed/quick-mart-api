<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class PermissionsExport
 *
 * Handles the logic for exporting permission records to an Excel or PDF file.
 */
class PermissionsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Default columns to export if none are explicitly specified.
     */
    private const DEFAULT_COLUMNS = [
        'id',
        'name',
        'guard_name',
        'description',
        'module',
        'is_active',
        'created_at',
        'updated_at',
    ];

    /**
     * PermissionsExport constructor.
     *
     * @param array<int> $ids Array of specific permission IDs to export.
     * @param array<string> $columns Array of specific columns to include in the export.
     * @param array<string, mixed> $filters Associative array of dynamic filters to apply to the query.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    )
    {
    }

    /**
     * Prepare the query for the export.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return Permission::query()
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->when(!empty($this->filters), fn(Builder $q) => $q->filter($this->filters))
            ->orderBy('name');
    }

    /**
     * Define the headings for the top row of the export file.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(fn(string $col) => ucfirst(str_replace('_', ' ', $col)), $columns);
    }

    /**
     * Map the permission model data to the corresponding export columns.
     *
     * @param Permission $row The current permission model instance being processed.
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'created_at' => $row->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at?->format('Y-m-d H:i:s'),
                default => $row->{$col},
            };
        }, $columns);
    }
}
