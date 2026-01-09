<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * BrandsExport
 *
 * Handles exporting brands to Excel.
 */
class BrandsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly array $ids = []
    ) {
    }

    /**
     * Get the collection of brands to export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Brand::query();

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
            'short_description',
            'image_url',
            'page_title',
        ];
    }

    /**
     * Map each brand to a row in the Excel file.
     *
     * @param Brand $brand
     * @return array
     */
    public function map($brand): array
    {
        return [
            $brand->name,
            $brand->short_description ?? '',
            $brand->image_url ?? '',
            $brand->page_title ?? '',
        ];
    }
}

