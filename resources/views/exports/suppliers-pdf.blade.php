<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers Export</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #1E2B2E; }
        .header h1 { font-size: 18px; color: #1E2B2E; font-weight: bold; margin-bottom: 5px; }
        .header .date { font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #1E2B2E; color: #fff; }
        th { padding: 8px 6px; text-align: left; font-weight: bold; font-size: 9px; border: 1px solid #1E2B2E; }
        td { padding: 6px; border: 1px solid #ddd; font-size: 9px; word-wrap: break-word; }
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; font-size: 8px; color: #666; }
        .badge-yes { background-color: #d4edda; color: #155724; padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-no { background-color: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .max-width-200 { max-width: 200px; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Suppliers Export</h1>
        <div class="date">Generated on: {{ date('Y-m-d H:i:s') }}</div>
        @if(count($suppliers) > 0)
            <div class="date">Total Records: {{ count($suppliers) }}</div>
        @endif
    </div>

    @if(count($suppliers) > 0)
        <table>
            <thead>
                <tr>
                    @php
                        $columnLabels = [
                            'id' => 'ID', 'name' => 'Name', 'company_name' => 'Company', 'vat_number' => 'VAT',
                            'email' => 'Email', 'phone_number' => 'Phone', 'address' => 'Address', 'city' => 'City',
                            'state' => 'State', 'postal_code' => 'Postal', 'country' => 'Country',
                            'opening_balance' => 'Opening Balance', 'image' => 'Image', 'is_active' => 'Active',
                            'created_at' => 'Created', 'updated_at' => 'Updated',
                        ];
                        $columnsToShow = empty($columns) ? array_keys($columnLabels) : $columns;
                    @endphp
                    @foreach($columnsToShow as $column)
                        <th>{{ $columnLabels[$column] ?? ucfirst(str_replace('_', ' ', $column)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    <tr>
                        @foreach($columnsToShow as $column)
                            <td class="max-width-200">
                                @switch($column)
                                    @case('id') {{ $supplier->id }} @break
                                    @case('name') {{ $supplier->name ?? '-' }} @break
                                    @case('company_name') {{ $supplier->company_name ?? '-' }} @break
                                    @case('vat_number') {{ $supplier->vat_number ?? '-' }} @break
                                    @case('email') {{ $supplier->email ?? '-' }} @break
                                    @case('phone_number') {{ $supplier->phone_number ?? '-' }} @break
                                    @case('address') {{ Str::limit($supplier->address ?? '-', 30) }} @break
                                    @case('city') {{ $supplier->city?->name ?? '-' }} @break
                                    @case('state') {{ $supplier->state?->name ?? '-' }} @break
                                    @case('postal_code') {{ $supplier->postal_code ?? '-' }} @break
                                    @case('country') {{ $supplier->country?->name ?? '-' }} @break
                                    @case('opening_balance') {{ $supplier->opening_balance ?? 0 }} @break
                                    @case('image') {{ Str::limit($supplier->image ?? '-', 20) }} @break
                                    @case('is_active') <span class="{{ $supplier->is_active ? 'badge-yes' : 'badge-no' }}">{{ $supplier->is_active ? 'Yes' : 'No' }}</span> @break
                                    @case('created_at') {{ $supplier->created_at?->format('Y-m-d H:i') ?? '-' }} @break
                                    @case('updated_at') {{ $supplier->updated_at?->format('Y-m-d H:i') ?? '-' }} @break
                                    @default -
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No suppliers to export.</p>
    @endif

    <div class="footer">
        <p>This is a system-generated report.</p>
    </div>
</body>
</html>
