<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * UnitsExport
 *
 * Handles exporting units to Excel.
 */
class UnitsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {
    }

    /**
     * Get the collection of units to export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Unit::query()->with('baseUnitRelation:id,code,name');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->orderBy('code')->get();
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
            'code' => 'Code',
            'name' => 'Name',
            'base_unit_name' => 'Base Unit',
            'operator' => 'Operator',
            'operation_value' => 'Operation Value',
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
     * Map each unit to a row in the Excel file.
     *
     * @param Unit $unit
     * @return array
     */
    public function map($unit): array
    {
        $defaultColumns = ['id', 'code', 'name', 'base_unit_name', 'operator', 'operation_value', 'is_active', 'created_at', 'updated_at'];
        $columnsToExport = empty($this->columns) ? $defaultColumns : $this->columns;

        $data = [];
        foreach ($columnsToExport as $column) {
            match ($column) {
                'id' => $data[] = $unit->id,
                'code' => $data[] = $unit->code,
                'name' => $data[] = $unit->name ?? '',
                'base_unit_name' => $data[] = $unit->baseUnitRelation?->name ?? '',
                'operator' => $data[] = $unit->operator ?? '*',
                'operation_value' => $data[] = $unit->operation_value ?? 1,
                'is_active' => $data[] = $unit->is_active ? 'Yes' : 'No',
                'created_at' => $data[] = $unit->created_at?->format('Y-m-d H:i:s') ?? '',
                'updated_at' => $data[] = $unit->updated_at?->format('Y-m-d H:i:s') ?? '',
                default => $data[] = '',
            };
        }

        return $data;
    }
}

