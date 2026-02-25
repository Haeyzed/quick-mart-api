<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Shift;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ShiftsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row): Shift
    {
        return new Shift([
            'name' => $row['name'],
            'start_time' => Carbon::parse($row['start_time'])->format('H:i'),
            'end_time' => Carbon::parse($row['end_time'])->format('H:i'),
            'late_time_limit' => Carbon::parse($row['late_time_limit'])->format('H:i'),
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:shifts,name'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'late_time_limit' => ['required'],
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
