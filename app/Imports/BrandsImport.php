<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * Class BrandsImport
 *
 * Imports brands using upsert logic based on name.
 */
class BrandsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * @param Row $row
     * @return void
     */
    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            return;
        }

        Brand::updateOrCreate(
            ['name' => $name],
            [
                'short_description' => isset($data['short_description']) ? trim((string)$data['short_description']) : null,
                'image_url'         => isset($data['image_url']) ? trim((string)$data['image_url']) : null,
                'page_title'        => isset($data['page_title']) ? trim((string)$data['page_title']) : null,
                // If updating, we keep it active; if creating, it defaults to true
                'is_active'         => true,
            ]
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
        ];
    }
}