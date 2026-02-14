<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromQuery, WithHeadings, WithMapping
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
        return Customer::query()
            ->with('customerGroup')
            ->when(!empty($this->ids), fn(Builder $q) => $q->whereIn('id', $this->ids))
            ->orderBy('name');
    }

    public function headings(): array
    {
        $labelMap = [
            'id' => 'ID',
            'name' => 'Name',
            'company_name' => 'Company Name',
            'email' => 'Email',
            'phone_number' => 'Phone Number',
            'customer_group' => 'Customer Group',
            'address' => 'Address',
            'city' => 'City',
            'country' => 'Country',
            'opening_balance' => 'Opening Balance',
            'deposit' => 'Deposit',
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

    public function map($customer): array
    {
        /** @var Customer $customer */
        $columnsToExport = $this->columns ?: [
            'id', 'name', 'company_name', 'email', 'phone_number', 'customer_group',
            'address', 'city', 'country', 'opening_balance', 'deposit', 'is_active', 'created_at', 'updated_at',
        ];

        $row = [];
        foreach ($columnsToExport as $col) {
            if ($col === 'customer_group') {
                $row[] = $customer->customerGroup?->name ?? '';
            } elseif ($col === 'is_active') {
                $row[] = $customer->is_active ? 'Active' : 'Inactive';
            } elseif ($col === 'created_at') {
                $row[] = $customer->created_at?->toDateTimeString();
            } elseif ($col === 'updated_at') {
                $row[] = $customer->updated_at?->toDateTimeString();
            } else {
                $row[] = $customer->{$col} ?? '';
            }
        }

        return $row;
    }
}
