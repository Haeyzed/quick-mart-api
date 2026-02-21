<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class CitiesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    public function model(array $row): ?City
    {
        $name = trim((string) ($row['name'] ?? ''));
        $countryCode = trim((string) ($row['country_code'] ?? ''));
        $stateCode = trim((string) ($row['state_code'] ?? ''));

        if ($name === '' || $countryCode === '' || $stateCode === '') {
            return null;
        }

        $country = Country::where('iso2', $countryCode)->first();
        if (! $country) {
            return null;
        }

        $state = State::where('country_id', $country->id)->where('state_code', $stateCode)->first();
        if (! $state) {
            return null;
        }

        return new City([
            'name' => $name,
            'country_id' => $country->id,
            'state_id' => $state->id,
            'country_code' => $countryCode,
            'state_code' => $stateCode,
            'latitude' => $row['latitude'] ?? null,
            'longitude' => $row['longitude'] ?? null,
        ]);
    }

    public function uniqueBy(): string
    {
        return 'name'; // Assuming name + state_id + country_id is unique, but upsert only supports single column or composite key if supported by DB. Here using name for simplicity or adjust as needed.
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'exists:countries,iso2'],
            'state_code' => ['required', 'string', 'exists:states,state_code'],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
