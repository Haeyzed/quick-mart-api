<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Language;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class LanguagesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    public function model(array $row): ?Language
    {
        $name = trim((string) ($row['name'] ?? ''));
        $code = trim((string) ($row['code'] ?? ''));

        if ($name === '' || $code === '') {
            return null;
        }

        return new Language([
            'code' => $code,
            'name' => $name,
            'name_native' => $row['name_native'] ?? null,
            'dir' => $row['dir'] ?? 'ltr',
        ]);
    }

    public function uniqueBy(): string
    {
        return 'code';
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:2'],
            'name' => ['required', 'string', 'max:255'],
            'dir' => ['nullable', 'in:ltr,rtl'],
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
