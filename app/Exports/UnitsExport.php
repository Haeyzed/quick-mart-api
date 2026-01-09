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
        private readonly array $ids = []
    ) {
    }

    /**
     * Get the collection of units to export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Unit::query()->with('baseUnitRelation:id,code');

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
        return [
            'code',
            'name',
            'baseunit',
            'operator',
            'operationvalue',
        ];
    }

    /**
     * Map each unit to a row in the Excel file.
     *
     * @param Unit $unit
     * @return array
     */
    public function map($unit): array
    {
        return [
            $unit->code,
            $unit->name ?? '',
            $unit->baseUnitRelation?->code ?? '',
            $unit->operator ?? '*',
            $unit->operation_value ?? 1,
        ];
    }
}

