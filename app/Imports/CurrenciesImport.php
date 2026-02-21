<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Country;
use App\Models\Currency;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class CurrenciesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    public function model(array $row): ?Currency
    {
        $name = trim((string) ($row['name'] ?? ''));
        $code = trim((string) ($row['code'] ?? ''));
        $countryCode = trim((string) ($row['country_code'] ?? ''));

        if ($name === '' || $code === '' || $countryCode === '') {
            return null;
        }

        $country = Country::where('iso2', $countryCode)->first();
        if (! $country) {
            return null;
        }

        return new Currency([
            'name' => $name,
            'code' => $code,
            'country_id' => $country->id,
            'symbol' => $row['symbol'] ?? null,
            'precision' => $row['precision'] ?? 2,
            'symbol_native' => $row['symbol_native'] ?? null,
            'symbol_first' => isset($row['symbol_first']) ? filter_var($row['symbol_first'], FILTER_VALIDATE_BOOLEAN) : true,
            'decimal_mark' => $row['decimal_mark'] ?? '.',
            'thousands_separator' => $row['thousands_separator'] ?? ',',
        ]);
    }

    public function uniqueBy(): string
    {
        return 'code';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'exists:countries,iso2'],
            'symbol' => ['nullable', 'string', 'max:255'],
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
