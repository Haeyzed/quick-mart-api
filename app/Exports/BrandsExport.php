<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports Brand data with spreadsheet styling suitable for PDF generation.
 */
class BrandsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents
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
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $columns = empty($this->columns) ? self::DEFAULT_COLUMNS : $this->columns;

        return array_map(function ($col) use ($row) {
            return match ($col) {
                'is_active' => $row->is_active ? 'Active' : 'Inactive',
                'created_at' => $row->created_at?->toDateTimeString(),
                'updated_at' => $row->updated_at?->toDateTimeString(),
                default => $row->{$col} ?? '',
            };
        }, $columns);
    }

    /**
     * Apply styles to the worksheet elements.
     *
     * @param Worksheet $sheet
     * @return array
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

                // 1. PAPER SIZE & ORIENTATION
                $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);

                // 2. FIT TO ONE PAGE WIDTH (Crucial for PDF)
                // This forces all columns to squeeze onto one page width,
                // while allowing infinite pages vertically.
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);

                // 3. TEXT WRAPPING FOR LONG COLUMNS
                // Assuming 'D' is short_description and 'F' is image_url based on your default columns.
                // We set specific widths so they don't get too narrow or too wide, then wrap them.
                $sheet->getColumnDimension('D')->setWidth(40); // Description
                $sheet->getColumnDimension('F')->setWidth(30); // Image URL

                $sheet->getStyle('D:F')->getAlignment()->setWrapText(true);

                // 4. BORDERS
                $cellRange = 'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow();
                $sheet->getDelegate()->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    // Optional: Vertical centering makes wrapped text look better
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
