<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Biller;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Biller entities with batching and upsert support.
 */
class BillersImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    /**
     * @param array<string, mixed> $row
     * @return Biller|null
     */
    public function model(array $row): ?Biller
    {
        $email = trim((string)($row['email'] ?? ''));

        if ($email === '') {
            return null;
        }

        return new Biller([
            'name' => $row['name'] ?? null,
            'email' => $email,
            'phone' => $row['phone'] ?? null,
            'company_name' => $row['company_name'] ?? null,
            'vat_number' => $row['vat_number'] ?? null,
            'address' => $row['address'] ?? null,
            'city' => $row['city'] ?? null,
            'state' => $row['state'] ?? null,
            'postal_code' => $row['postal_code'] ?? null,
            'country' => $row['country'] ?? null,
            'image_url' => $row['image_url'] ?? null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    /**
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'email';
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
