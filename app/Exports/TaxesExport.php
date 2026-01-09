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
        private readonly array $ids = []
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
        return [
            'name',
            'rate',
        ];
    }

    /**
     * Map each tax to a row in the Excel file.
     *
     * @param Tax $tax
     * @return array
     */
    public function map($tax): array
    {
        return [
            $tax->name,
            $tax->rate ?? 0,
        ];
    }
}

