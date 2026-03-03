<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\DocumentType;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class DocumentTypesImport
 *
 * Handles the logic for importing Document Types from an uploaded Excel or CSV file.
 * Utilizes upserts to create, update, or restore soft-deleted records automatically.
 */
class DocumentTypesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    WithUpserts,
    SkipsEmptyRows
{
    /**
     * Map a row from the spreadsheet to a DocumentType model.
     * Setting 'deleted_at' to null ensures restoration if soft-deleted previously.
     *
     * @param array<string, mixed> $row
     * @return DocumentType|null
     */
    public function model(array $row): ?DocumentType
    {
        $name = trim((string)($row['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        return new DocumentType([
            'name' => $name,
            'code' => $row['code'] ?? null,
            'requires_expiry' => filter_var($row['requires_expiry'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'deleted_at' => null, // Restores the record if it was soft-deleted
        ]);
    }

    /**
     * Specify the unique column to be used for the upsert operation.
     */
    public function uniqueBy(): string
    {
        return 'name';
    }

    /**
     * Define the validation rules for the imported rows.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'requires_expiry' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
