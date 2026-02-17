<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Customers export (Excel/PDF). Same pattern as BillersExport.
 */
class CustomersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private const DEFAULT_COLUMNS = [
        'id',
        'name',
        'company_name',
        'email',
        'phone_number',
        'customer_group',
        'address',
        'city',
        'state',
        'country',
        'opening_balance',
        'deposit',
        'is_active',
        'created_at',
        'updated_at',
    ];

    /**
     * @param  array<int>  $ids
     * @param  array<string>  $columns
     * @param  array<string, string>  $filters  Optional filters (e.g. start_date, end_date) for scopeFilter
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {
    }

    public function query(): Builder
    {
        return Customer::query()
            ->with(['customerGroup', 'country', 'state', 'city'])
            ->when(! empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->orderBy('name');
    }

    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(
            fn (string $col) => ucfirst(str_replace('_', ' ', $col)),
            $columns
        );
    }

    /**
     * @param  Customer  $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function (string $col) use ($row) {
            return match ($col) {
                'customer_group' => $row->customerGroup?->name ?? '',
                'city' => $row->city?->name ?? '',
                'state' => $row->state?->name ?? '',
                'country' => $row->country?->name ?? '',
                'is_active' => $row->is_active ? 'Yes' : 'No',
                'created_at' => $row->created_at?->toDateTimeString(),
                'updated_at' => $row->updated_at?->toDateTimeString(),
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }
}
