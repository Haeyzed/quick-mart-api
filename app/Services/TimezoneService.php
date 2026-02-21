<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\TimezonesExport;
use App\Imports\TimezonesImport;
use App\Models\Timezone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class TimezoneService
 * Handles business logic for Timezones (World reference data).
 */
class TimezoneService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated timezones.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedTimezones(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Timezone::query()
            ->with('country:id,name,iso2')
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of timezone options grouped by region (value/label format).
     *
     * @return array<int, array{region: string, options: array<int, array{value: int, label: string, country_id: int|null}>}>
     */
    public function getOptions(): array
    {
        $timezones = Timezone::query()
            ->select('id', 'name', 'country_id')
            ->orderBy('name')
            ->get();

        $grouped = $timezones->groupBy(function (Timezone $timezone): string {
            return str_contains($timezone->name, '/')
                ? explode('/', $timezone->name, 2)[0]
                : 'Other';
        });

        $grouped = $grouped->sortKeys();

        return $grouped->map(function (Collection $items, string $region): array {
            return [
                'region' => $region,
                'options' => $items->map(fn (Timezone $tz) => [
                    'value' => $tz->id,
                    'label' => $tz->name,
                    'country_id' => $tz->country_id,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    public function createTimezone(array $data): Timezone
    {
        return DB::transaction(function () use ($data) {
            return Timezone::query()->create($data);
        });
    }

    public function updateTimezone(Timezone $timezone, array $data): Timezone
    {
        return DB::transaction(function () use ($timezone, $data) {
            $timezone->update($data);

            return $timezone->fresh();
        });
    }

    public function deleteTimezone(Timezone $timezone): void
    {
        DB::transaction(function () use ($timezone) {
            $timezone->delete();
        });
    }

    public function bulkDeleteTimezones(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Timezone::query()->whereIn('id', $ids)->delete();
        });
    }

    public function importTimezones(UploadedFile $file): void
    {
        ExcelFacade::import(new TimezonesImport, $file);
    }

    public function download(): string
    {
        $fileName = 'timezones-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template timezones not found.');
        }

        return $path;
    }

    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'timezones_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new TimezonesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
