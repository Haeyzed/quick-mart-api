<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Biller;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BillersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    ) {}

    public function query(): Builder
    {
        return Biller::query()
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('company_name');
    }

    public function headings(): array
    {
        $labelMap = [
            'id' => 'ID',
            'name' => 'Name',
            'company_name' => 'Company Name',
            'vat_number' => 'VAT Number',
            'email' => 'Email',
            'phone_number' => 'Phone Number',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'postal_code' => 'Postal Code',
            'country' => 'Country',
            'image' => 'Image',
            'is_active' => 'Status',
            'created_at' => 'Date Created',
            'updated_at' => 'Last Updated',
        ];

        if (empty($this->columns)) {
            return array_values($labelMap);
        }

        return array_map(
            fn ($col) => $labelMap[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    public function map($biller): array
    {
        /** @var Biller $biller */
        $columnsToExport = $this->columns ?: [
            'id', 'name', 'company_name', 'vat_number', 'email', 'phone_number',
            'address', 'city', 'state', 'postal_code', 'country', 'image',
            'is_active', 'created_at', 'updated_at',
        ];

        $row = [];
        foreach ($columnsToExport as $col) {
            if ($col === 'is_active') {
                $row[] = $biller->is_active ? 'Active' : 'Inactive';
            } elseif ($col === 'created_at') {
                $row[] = $biller->created_at?->toDateTimeString();
            } elseif ($col === 'updated_at') {
                $row[] = $biller->updated_at?->toDateTimeString();
            } else {
                $row[] = $biller->{$col} ?? '';
            }
        }

        return $row;
    }
}
