<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports Brand data with PDF-optimized styling and external image support.
 */
class BrandsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, WithDrawings
{
    use Exportable;

    /**
     * Default columns to export if none are specified.
     *
     * @var array<string>
     */
    private const DEFAULT_COLUMNS = [
        'id',
        'name',
        'slug',
        'short_description',
        'page_title',
        'image_url',
        'is_active',
        'created_at',
        'updated_at',
    ];

    /**
     * Create a new export instance.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string> $filters
     */
    public function __construct(
        private readonly array $ids = [],
        private readonly array $columns = [],
        private readonly array $filters = [],
    ) {
    }

    /**
     * Prepare the query for the export.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return Brand::query()
            ->when(!empty($this->ids), fn (Builder $q) => $q->whereIn('id', $this->ids))
            ->filter($this->filters)
            ->orderBy('name');
    }

    /**
     * Define the headings for the export.
     *
     * @return array<string>
     */
    public function headings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(
            fn (string $col) => ucfirst(str_replace('_', ' ', $col)),
            $columns
        );
    }

    /**
     * Map the data for each row.
     *
     * @param Brand $row
     * @return array<mixed>
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'created_at' => $row->created_at?->toDateTimeString(),
                'updated_at' => $row->updated_at?->toDateTimeString(),
                'image_url' => '',
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }

    /**
     * Add drawings (images) to the worksheet from external URLs.
     *
     * @return array<Drawing>
     */
    public function drawings(): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;
        $imageColIndex = array_search('image_url', $columns);

        if ($imageColIndex === false) {
            return [];
        }

        $colLetter = Coordinate::stringFromColumnIndex($imageColIndex + 1);
        $brands = $this->query()->get();
        $drawings = [];

        foreach ($brands as $index => $brand) {
            if (empty($brand->image_url) || !filter_var($brand->image_url, FILTER_VALIDATE_URL)) {
                continue;
            }

            try {
                $tempImage = tempnam(sys_get_temp_dir(), 'brand_export_');
                $imageContent = @file_get_contents($brand->image_url);

                if ($imageContent === false) {
                    continue;
                }

                file_put_contents($tempImage, $imageContent);

                $drawing = new Drawing();
                $drawing->setName((string) $brand->name);
                $drawing->setDescription((string) $brand->name);
                $drawing->setPath($tempImage);
                $drawing->setHeight(50);
                $drawing->setCoordinates($colLetter . ($index + 2));
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);

                $drawings[] = $drawing;

            } catch (\Throwable $e) {
                continue;
            }
        }

        return $drawings;
    }

    /**
     * Apply styles to the worksheet elements.
     *
     * @param Worksheet $sheet
     * @return array<mixed>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '4472C4'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    /**
     * Register events to manipulate the sheet after it is created.
     *
     * @return array<string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $pageSetup = $sheet->getPageSetup();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);

                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(60);
                }

                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
            },
        ];
    }
}
