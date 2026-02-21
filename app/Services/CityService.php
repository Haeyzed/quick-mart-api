<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CitiesExport;
use App\Imports\CitiesImport;
use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class CityService
 * Handles business logic for Cities (World reference data).
 */
class CityService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated cities.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCities(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return City::query()
            ->with(['country:id,name,iso2', 'state:id,name,state_code'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of city options (value/label format).
     */
    public function getOptions(): Collection
    {
        return City::query()
            ->select('id', 'name', 'state_id', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (City $city) => [
                'value' => $city->id,
                'label' => $city->name,
                'state_id' => $city->state_id,
                'country_id' => $city->country_id,
            ]);
    }

    public function createCity(array $data): City
    {
        return DB::transaction(function () use ($data) {
            return City::query()->create($data);
        });
    }

    public function updateCity(City $city, array $data): City
    {
        return DB::transaction(function () use ($city, $data) {
            $city->update($data);

            return $city->fresh();
        });
    }

    public function deleteCity(City $city): void
    {
        DB::transaction(function () use ($city) {
            $city->delete();
        });
    }

    public function bulkDeleteCities(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return City::query()->whereIn('id', $ids)->delete();
        });
    }

    public function importCities(UploadedFile $file): void
    {
        ExcelFacade::import(new CitiesImport, $file);
    }

    public function download(): string
    {
        $fileName = 'cities-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template cities not found.');
        }

        return $path;
    }

    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'cities_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new CitiesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
