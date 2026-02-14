<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use OwenIt\Auditing\Models\Audit;

/**
 * Excel export for Audit entities.
 *
 * Exports audits by ID or all when ids is empty. Supports column selection.
 */
class AuditsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param array<int> $ids Audit IDs to export. Empty array exports all.
     * @param array<string> $columns Column keys to include.
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    )
    {
    }

    /**
     * @return Builder<Audit>
     */
    public function query(): Builder
    {
        return Audit::query()
            ->with('user')
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('id');
    }

    /**
     * @return array<string>
     */
    public function headings(): array
    {
        $labelMap = [
            'id' => 'ID',
            'event' => 'Event',
            'auditable_type' => 'Model',
            'auditable_id' => 'Model ID',
            'user_name' => 'User',
            'ip_address' => 'IP Address',
            'created_at' => 'Date',
        ];

        if (empty($this->columns)) {
            return array_values($labelMap);
        }

        return array_map(
            fn($col) => $labelMap[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    /**
     * @param Audit $audit
     * @return array<string|int|null>
     */
    public function map($audit): array
    {
        $columnsToExport = $this->columns ?: [
            'id', 'event', 'auditable_type', 'auditable_id',
            'user_name', 'ip_address', 'created_at',
        ];

        return array_map(fn($col) => match ($col) {
            'user_name' => $audit->user?->name ?? '—',
            'created_at' => $audit->created_at?->toDateTimeString(),
            default => $audit->{$col} ?? '',
        }, $columnsToExport);
    }
}
