<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class DocumentTypesExport
 *
 * Handles exporting Document Types to Excel/PDF.
 */
class DocumentTypesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private const DEFAULT_COLUMNS = [
        'id',
        'name',
        'code',
        'requires_expiry',
        'is_active',
        'created_at',
        'updated_at',
    ];

    /**
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {}

    public function query(): Builder
    {
        return DocumentType::query()
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->orderBy('name');
    }

    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(fn (string $col) => ucfirst(str_replace('_', ' ', $col)), $columns);
    }

    /**
     * @param DocumentType $row
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'requires_expiry' => $row->requires_expiry ? 'Yes' : 'No',
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'created_at' => $row->created_at?->toDateTimeString(),
                'updated_at' => $row->updated_at?->toDateTimeString(),
                default => $row->{$col},
            };
        }, $columns);
    }
}
