<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CountriesExport;
use App\Imports\CountriesImport;
use App\Models\Country;
use App\Models\State;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class CountryService
 * Handles business logic for Countries (World reference data).
 */
class CountryService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated countries.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCountries(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Country::query()
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of country options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Country::query()
            ->select('id', 'name', 'iso2')
            ->orderBy('name')
            ->get()
            ->map(fn (Country $country) => [
                'value' => $country->id,
                'label' => $country->name,
                'iso2' => $country->iso2,
            ]);
    }

    /**
     * Get state options (value/label) for a given country.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getStateOptionsByCountry(Country $country): Collection
    {
        return $country->states()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (State $state) => [
                'value' => $state->id,
                'label' => $state->name,
            ]);
    }

    public function createCountry(array $data): Country
    {
        return DB::transaction(function () use ($data) {
            return Country::query()->create($data);
        });
    }

    public function updateCountry(Country $country, array $data): Country
    {
        return DB::transaction(function () use ($country, $data) {
            $country->update($data);

            return $country->fresh();
        });
    }

    public function deleteCountry(Country $country): void
    {
        DB::transaction(function () use ($country) {
            $country->delete();
        });
    }

    public function bulkDeleteCountries(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Country::query()->whereIn('id', $ids)->delete();
        });
    }

    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Country::query()->whereIn('id', $ids)->update(['status' => $isActive ? 1 : 0]);
    }

    public function importCountries(UploadedFile $file): void
    {
        ExcelFacade::import(new CountriesImport, $file);
    }

    public function download(): string
    {
        $fileName = 'countries-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template countries not found.');
        }

        return $path;
    }

    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'countries_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new CountriesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
