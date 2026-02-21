<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CurrenciesExport;
use App\Imports\CurrenciesImport;
use App\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class CurrencyService
 * Handles business logic for Currencies (World reference data).
 */
class CurrencyService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated currencies.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCurrencies(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Currency::query()
            ->with('country:id,name,iso2')
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of currency options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Currency::query()
            ->select('id', 'name', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Currency $currency) => [
                'value' => $currency->id,
                'label' => $currency->name,
                'country_id' => $currency->country_id,
            ]);
    }

    public function createCurrency(array $data): Currency
    {
        return DB::transaction(function () use ($data) {
            return Currency::query()->create($data);
        });
    }

    public function updateCurrency(Currency $currency, array $data): Currency
    {
        return DB::transaction(function () use ($currency, $data) {
            $currency->update($data);

            return $currency->fresh();
        });
    }

    public function deleteCurrency(Currency $currency): void
    {
        DB::transaction(function () use ($currency) {
            $currency->delete();
        });
    }

    public function bulkDeleteCurrencies(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Currency::query()->whereIn('id', $ids)->delete();
        });
    }

    public function importCurrencies(UploadedFile $file): void
    {
        ExcelFacade::import(new CurrenciesImport, $file);
    }

    public function download(): string
    {
        $fileName = 'currencies-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template currencies not found.');
        }

        return $path;
    }

    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'currencies_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new CurrenciesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
