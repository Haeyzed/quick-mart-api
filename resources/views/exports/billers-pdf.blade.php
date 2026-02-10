<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billers Export</title>
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
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-yes { background-color: #d4edda; color: #155724; }
        .badge-no { background-color: #f8d7da; color: #721c24; }
        .max-width-200 { max-width: 200px; word-wrap: break-word; overflow-wrap: break-word; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Billers Export</h1>
        <div class="date">Generated on: {{ date('Y-m-d H:i:s') }}</div>
        @if(count($billers) > 0)
            <div class="date">Total Records: {{ count($billers) }}</div>
        @endif
    </div>

    @if(count($billers) > 0)
        <table>
            <thead>
                <tr>
                    @php
                        $columnLabels = [
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
                            'is_active' => 'Is Active',
                            'created_at' => 'Created At',
                            'updated_at' => 'Updated At',
                        ];
                        $columnsToShow = empty($columns) ? array_keys($columnLabels) : $columns;
                    @endphp
                    @foreach($columnsToShow as $column)
                        <th>{{ $columnLabels[$column] ?? ucfirst(str_replace('_', ' ', $column)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($billers as $biller)
                    <tr>
                        @foreach($columnsToShow as $column)
                            <td class="max-width-200">
                                @switch($column)
                                    @case('id') {{ $biller->id }} @break
                                    @case('name') {{ $biller->name ?? '-' }} @break
                                    @case('company_name') {{ $biller->company_name ?? '-' }} @break
                                    @case('vat_number') {{ $biller->vat_number ?? '-' }} @break
                                    @case('email') {{ $biller->email ?? '-' }} @break
                                    @case('phone_number') {{ $biller->phone_number ?? '-' }} @break
                                    @case('address') {{ Str::limit($biller->address ?? '-', 40) }} @break
                                    @case('city') {{ $biller->city ?? '-' }} @break
                                    @case('state') {{ $biller->state ?? '-' }} @break
                                    @case('postal_code') {{ $biller->postal_code ?? '-' }} @break
                                    @case('country') {{ $biller->country ?? '-' }} @break
                                    @case('image') {{ Str::limit($biller->image ?? '-', 30) }} @break
                                    @case('is_active')
                                        <span class="badge {{ $biller->is_active ? 'badge-yes' : 'badge-no' }}">{{ $biller->is_active ? 'Yes' : 'No' }}</span>
                                        @break
                                    @case('created_at') {{ $biller->created_at?->format('Y-m-d H:i:s') ?? '-' }} @break
                                    @case('updated_at') {{ $biller->updated_at?->format('Y-m-d H:i:s') ?? '-' }} @break
                                    @default -
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center">No billers to export.</p>
    @endif

    <div class="footer">
        <p>This is a system-generated report.</p>
    </div>
</body>
</html>
