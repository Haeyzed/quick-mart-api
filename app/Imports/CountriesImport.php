<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Country;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class CountriesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    public function model(array $row): ?Country
    {
        $name = trim((string) ($row['name'] ?? ''));
        $iso2 = trim((string) ($row['iso2'] ?? ''));

        if ($name === '' || $iso2 === '') {
            return null;
        }

        return new Country([
            'iso2' => $iso2,
            'name' => $name,
            'status' => isset($row['status']) ? (filter_var($row['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : 1,
            'phone_code' => $row['phone_code'] ?? null,
            'iso3' => $row['iso3'] ?? null,
            'region' => $row['region'] ?? null,
            'subregion' => $row['subregion'] ?? null,
            'native' => $row['native'] ?? null,
            'latitude' => $row['latitude'] ?? null,
            'longitude' => $row['longitude'] ?? null,
            'emoji' => $row['emoji'] ?? null,
            'emojiU' => $row['emoji_u'] ?? null,
        ]);
    }

    public function uniqueBy(): string
    {
        return 'iso2';
    }

    public function rules(): array
    {
        return [
            'iso2' => ['required', 'string', 'max:2'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
            'phone_code' => ['nullable', 'string'],
            'iso3' => ['nullable', 'string', 'max:3'],
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
