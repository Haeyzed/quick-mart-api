<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Holiday;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HolidaysImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    public function model(array $row): ?Holiday
    {
        $fromDate = $row['from_date'] ?? null;
        $toDate = $row['to_date'] ?? null;
        if (! $fromDate || ! $toDate) {
            return null;
        }
        $userId = isset($row['user_id']) ? (int) $row['user_id'] : 1;
        return new Holiday([
            'user_id' => $userId,
            'from_date' => date('Y-m-d', strtotime(str_replace('/', '-', $fromDate))),
            'to_date' => date('Y-m-d', strtotime(str_replace('/', '-', $toDate))),
            'note' => $row['note'] ?? null,
            'is_approved' => isset($row['is_approved']) ? filter_var($row['is_approved'], FILTER_VALIDATE_BOOLEAN) : false,
            'recurring' => isset($row['recurring']) ? filter_var($row['recurring'], FILTER_VALIDATE_BOOLEAN) : false,
            'region' => $row['region'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'note' => ['nullable', 'string', 'max:500'],
            'is_approved' => ['nullable', 'boolean'],
            'recurring' => ['nullable', 'boolean'],
            'region' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
