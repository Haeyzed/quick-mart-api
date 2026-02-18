<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = []
    )
    {
    }

    public function query(): Builder
    {
        return Supplier::query()
            ->with(['country:id,name', 'state:id,name', 'city:id,name'])
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
            'opening_balance' => 'Opening Balance',
            'image' => 'Image',
            'is_active' => 'Status',
            'created_at' => 'Date Created',
            'updated_at' => 'Last Updated',
        ];

        if (empty($this->columns)) {
            return array_values($labelMap);
        }

        return array_map(
            fn($col) => $labelMap[$col] ?? ucfirst(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    public function map($supplier): array
    {
        /** @var Supplier $supplier */
        $columnsToExport = $this->columns ?: [
            'id', 'name', 'company_name', 'vat_number', 'email', 'phone_number',
            'address', 'city', 'state', 'postal_code', 'country', 'opening_balance', 'image',
            'is_active', 'created_at', 'updated_at',
        ];

        $row = [];
        foreach ($columnsToExport as $col) {
            if ($col === 'is_active') {
                $row[] = $supplier->is_active ? 'Active' : 'Inactive';
            } elseif ($col === 'created_at') {
                $row[] = $supplier->created_at?->toDateTimeString();
            } elseif ($col === 'updated_at') {
                $row[] = $supplier->updated_at?->toDateTimeString();
            } elseif ($col === 'city') {
                $row[] = $supplier->city?->name ?? '';
            } elseif ($col === 'state') {
                $row[] = $supplier->state?->name ?? '';
            } elseif ($col === 'country') {
                $row[] = $supplier->country?->name ?? '';
            } else {
                $row[] = $supplier->{$col} ?? '';
            }
        }

        return $row;
    }
}
