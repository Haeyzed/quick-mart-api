<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\StatesExport;
use App\Imports\StatesImport;
use App\Models\City;
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
 * Class StateService
 * Handles business logic for States (World reference data).
 */
class StateService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated states.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedStates(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return State::query()
            ->with('country:id,name,iso2')
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of state options (value/label format).
     */
    public function getOptions(): Collection
    {
        return State::query()
            ->select('id', 'name', 'state_code', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (State $state) => [
                'value' => $state->id,
                'label' => $state->name,
                'state_code' => $state->state_code,
                'country_id' => $state->country_id,
            ]);
    }

    /**
     * Get city options (value/label) for a given state.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getCityOptionsByState(State $state): Collection
    {
        return $state->cities()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (City $city) => [
                'value' => $city->id,
                'label' => $city->name,
            ]);
    }

    public function createState(array $data): State
    {
        return DB::transaction(function () use ($data) {
            return State::query()->create($data);
        });
    }

    public function updateState(State $state, array $data): State
    {
        return DB::transaction(function () use ($state, $data) {
            $state->update($data);

            return $state->fresh();
        });
    }

    public function deleteState(State $state): void
    {
        DB::transaction(function () use ($state) {
            $state->delete();
        });
    }

    public function bulkDeleteStates(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return State::query()->whereIn('id', $ids)->delete();
        });
    }

    public function importStates(UploadedFile $file): void
    {
        ExcelFacade::import(new StatesImport, $file);
    }

    public function download(): string
    {
        $fileName = 'states-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template states not found.');
        }

        return $path;
    }

    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'states_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new StatesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
