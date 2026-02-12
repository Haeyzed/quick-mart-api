<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Report Export' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #1E2B2E; }
        .header h1 { font-size: 18px; color: #1E2B2E; font-weight: bold; margin-bottom: 5px; }
        .header .date { font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #1E2B2E; color: #fff; }
        th { padding: 8px 6px; text-align: left; font-weight: bold; font-size: 9px; border: 1px solid #1E2B2E; }
        td { padding: 6px; border: 1px solid #ddd; font-size: 9px; }
        .text-right { text-align: right; }
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; font-size: 8px; color: #666; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Report Export' }}</h1>
        <div class="date">Generated on: {{ date('Y-m-d H:i:s') }}</div>
        @if(count($rows) > 0)
            <div class="date">Total Records: {{ count($rows) }}</div>
        @endif
    </div>

    @php
        $columnsToShow = empty($columns) ? array_keys($columnLabels ?? []) : $columns;
        $labels = $columnLabels ?? [];
    @endphp

    @if(count($rows) > 0)
        <table>
            <thead>
                <tr>
                    @foreach($columnsToShow as $column)
                        <th class="@if(in_array($column, $rightAlignColumns ?? [])) text-right @endif">
                            {{ $labels[$column] ?? ucfirst(str_replace('_', ' ', $column)) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($columnsToShow as $column)
                            <td class="@if(in_array($column, $rightAlignColumns ?? [])) text-right @endif">
                                {{ $row[$column] ?? '—' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center">No records to export.</p>
    @endif

    <div class="footer">
        <p>This is a system-generated report.</p>
    </div>
</body>
</html>
