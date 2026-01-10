<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Tax;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * TaxesExport
 *
 * Handles exporting taxes to Excel.
 */
class TaxesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {
    }

    /**
     * Get the collection of taxes to export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Tax::query();

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Define the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        $columnLabels = [
            'id' => 'ID',
            'name' => 'Name',
            'rate' => 'Rate (%)',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        if (empty($this->columns)) {
            return array_values($columnLabels);
        }

        return array_map(fn($col) => $columnLabels[$col] ?? ucfirst(str_replace('_', ' ', $col)), $this->columns);
    }

    /**
     * Map each tax to a row in the Excel file.
     *
     * @param Tax $tax
     * @return array
     */
    public function map($tax): array
    {
        $defaultColumns = ['id', 'name', 'rate', 'is_active', 'created_at', 'updated_at'];
        $columnsToExport = empty($this->columns) ? $defaultColumns : $this->columns;

        $data = [];
        foreach ($columnsToExport as $column) {
            match ($column) {
                'id' => $data[] = $tax->id,
                'name' => $data[] = $tax->name,
                'rate' => $data[] = $tax->rate ?? 0,
                'is_active' => $data[] = $tax->is_active ? 'Yes' : 'No',
                'created_at' => $data[] = $tax->created_at?->format('Y-m-d H:i:s') ?? '',
                'updated_at' => $data[] = $tax->updated_at?->format('Y-m-d H:i:s') ?? '',
                default => $data[] = '',
            };
        }

        return $data;
    }
}

