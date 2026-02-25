<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Attendance;
use App\Enums\AttendanceStatusEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Class AttendancesImport
 *
 * Handles the logic for importing attendance records from an uploaded Excel or CSV file.
 * Processes rows in batches and chunks to optimize memory usage.
 */
class AttendancesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /**
     * Map a row from the spreadsheet to an Attendance model.
     *
     * @param array<string, mixed> $row
     * @return Attendance
     */
    public function model(array $row): Attendance
    {
        // 1. Fetch Global Settings
        $hrmSetting = DB::table('hrm_settings')->latest()->first();
        $defaultCheckin = $hrmSetting ? $hrmSetting->checkin : '08:00:00';

        // 2. Resolve Check-in
        $checkinTime = !empty($row['checkin'])
            ? Carbon::parse($row['checkin'])
            : Carbon::parse($defaultCheckin);

        // 3. Resolve Status
        $statusStr = strtolower((string)($row['status'] ?? ''));

        if (!in_array($statusStr, ['present', 'late', 'absent'])) {
            $statusValue = $checkinTime->lte(Carbon::parse($defaultCheckin))
                ? AttendanceStatusEnum::PRESENT->value
                : AttendanceStatusEnum::LATE->value;
        } else {
            $statusValue = $statusStr;
        }

        return new Attendance([
            'date' => Carbon::parse($row['date'])->format('Y-m-d'),
            'employee_id' => $row['employee_id'],
            'user_id' => Auth::id(),
            'checkin' => $checkinTime->format('H:i:s'),
            'checkout' => !empty($row['checkout']) ? Carbon::parse($row['checkout'])->format('H:i:s') : null,
            'status' => $statusValue,
            'note' => $row['note'] ?? null,
        ]);
    }

    /**
     * Define the validation rules for the imported rows.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'checkin' => ['required'],
            'checkout' => ['nullable'],
            'status' => ['nullable', 'string', 'in:present,late,absent'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Determine the batch size for database inserts.
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Determine the chunk size for reading the spreadsheet.
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
