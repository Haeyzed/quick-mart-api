<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1E2B2E;
        }

        .header h1 {
            font-size: 18px;
            color: #1E2B2E;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .date {
            font-size: 9px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background-color: #1E2B2E;
            color: #fff;
        }

        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #1E2B2E;
        }

        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9px;
            word-wrap: break-word;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }

        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-yes {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-no {
            background-color: #f8d7da;
            color: #721c24;
        }

        .max-width-200 {
            max-width: 200px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Categories Export</h1>
        <div class="date">Generated on: {{ date('Y-m-d H:i:s') }}</div>
        @if(count($categories) > 0)
            <div class="date">Total Records: {{ count($categories) }}</div>
        @endif
    </div>

    @if(count($categories) > 0)
        <table>
            <thead>
                <tr>
                    @php
                        $columnLabels = [
                            'id' => 'ID',
                            'name' => 'Name',
                            'slug' => 'Slug',
                            'short_description' => 'Short Description',
                            'parent_name' => 'Parent Category',
                            'featured' => 'Featured',
                            'is_active' => 'Is Active',
                            'is_sync_disable' => 'Is Sync Disabled',
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
                @foreach($categories as $category)
                    <tr>
                        @foreach($columnsToShow as $column)
                            <td class="max-width-200">
                                @switch($column)
                                    @case('id')
                                        {{ $category->id }}
                                        @break
                                    @case('name')
                                        {{ $category->name }}
                                        @break
                                    @case('slug')
                                        {{ $category->slug ?? '-' }}
                                        @break
                                    @case('short_description')
                                        {{ Str::limit($category->short_description ?? '-', 50) }}
                                        @break
                                    @case('parent_name')
                                        {{ $category->parent?->name ?? '-' }}
                                        @break
                                    @case('featured')
                                        <span class="badge {{ $category->featured ? 'badge-yes' : 'badge-no' }}">
                                            {{ $category->featured ? 'Yes' : 'No' }}
                                        </span>
                                        @break
                                    @case('is_active')
                                        <span class="badge {{ $category->is_active ? 'badge-yes' : 'badge-no' }}">
                                            {{ $category->is_active ? 'Yes' : 'No' }}
                                        </span>
                                        @break
                                    @case('is_sync_disable')
                                        <span class="badge {{ $category->is_sync_disable ? 'badge-no' : 'badge-yes' }}">
                                            {{ $category->is_sync_disable ? 'Yes' : 'No' }}
                                        </span>
                                        @break
                                    @case('created_at')
                                        {{ $category->created_at?->format('Y-m-d H:i:s') ?? '-' }}
                                        @break
                                    @case('updated_at')
                                        {{ $category->updated_at?->format('Y-m-d H:i:s') ?? '-' }}
                                        @break
                                    @default
                                        -
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center">No categories to export.</p>
    @endif

    <div class="footer">
        <p>This is a system-generated report. Please do not reply to this email.</p>
    </div>
</body>
</html>

