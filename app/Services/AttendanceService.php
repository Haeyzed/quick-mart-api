<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatusEnum;
use App\Exports\AttendancesExport;
use App\Imports\AttendancesImport;
use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

/**
 * Class AttendanceService
 *
 * Handles all core business logic and database interactions for Attendances.
 */
class AttendanceService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated attendances based on filters.
     * Inherits legacy access control logic limiting regular staff to their own records.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedAttendances(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $generalSetting = DB::table('general_settings')->latest()->first();

        if ($generalSetting?->staff_access === 'own') {
            $filters['user_id'] = Auth::id();
        }

        return Attendance::query()
            ->with(['employee', 'user'])
            ->filter($filters)
            ->latest('date')
            ->paginate($perPage);
    }

    /**
     * Create multiple attendance records simultaneously.
     * Incorporates legacy logic to auto-calculate 'late' vs 'present' based on global HrmSettings.
     * Utilizes updateOrCreate to seamlessly prevent duplicate records for the same day.
     *
     * @param array<string, mixed> $data
     * @return Collection
     */
    public function createAttendances(array $data): Collection
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();
            $createdRecords = collect();

            $date = Carbon::parse($data['date'])->format('Y-m-d');
            $checkinTime = Carbon::parse($data['checkin']);
            $checkoutTime = !empty($data['checkout']) ? Carbon::parse($data['checkout'])->format('H:i:s') : null;

            // Auto-calculate status if omitted by comparing against system-wide expected checkin
            $status = $data['status'] ?? null;
            if (!$status) {
                $hrmSetting = DB::table('hrm_settings')->latest()->first();
                $expectedCheckin = Carbon::parse($hrmSetting ? $hrmSetting->checkin : '08:00:00');

                $status = $checkinTime->lte($expectedCheckin)
                    ? AttendanceStatusEnum::PRESENT->value
                    : AttendanceStatusEnum::LATE->value;
            } elseif ($status instanceof AttendanceStatusEnum) {
                $status = $status->value;
            }

            foreach ($data['employee_ids'] as $employeeId) {
                $attendance = Attendance::query()->updateOrCreate(
                    ['date' => $date, 'employee_id' => $employeeId],
                    [
                        'user_id' => $userId,
                        'checkin' => $checkinTime->format('H:i:s'),
                        'checkout' => $checkoutTime,
                        'status' => $status,
                        'note' => $data['note'] ?? null,
                    ]
                );

                $createdRecords->push($attendance);
            }

            return $createdRecords;
        });
    }

    /**
     * Update an existing attendance record.
     *
     * @param  Attendance  $attendance
     * @param  array<string, mixed>  $data
     * @return Attendance
     */
    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        return DB::transaction(function () use ($attendance, $data) {
            // Auto-recalculate status if checkin changed but status was omitted
            if (isset($data['checkin']) && empty($data['status'])) {
                $hrmSetting = DB::table('hrm_settings')->latest()->first();
                $expectedCheckin = Carbon::parse($hrmSetting ? $hrmSetting->checkin : '08:00:00');
                $actualCheckin = Carbon::parse($data['checkin']);

                $data['status'] = $actualCheckin->lte($expectedCheckin)
                    ? AttendanceStatusEnum::PRESENT->value
                    : AttendanceStatusEnum::LATE->value;
            }

            if (isset($data['status']) && $data['status'] instanceof AttendanceStatusEnum) {
                $data['status'] = $data['status']->value;
            }

            $attendance->update($data);

            return $attendance->fresh(['employee', 'user']);
        });
    }

    /**
     * Delete an attendance record.
     *
     * @param  Attendance  $attendance
     * @return void
     */
    public function deleteAttendance(Attendance $attendance): void
    {
        DB::transaction(function () use ($attendance) {
            $attendance->delete();
        });
    }

    /**
     * Bulk delete multiple attendance records.
     *
     * @param  array<int>  $ids
     * @return int
     */
    public function bulkDeleteAttendances(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Attendance::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Update the status for multiple attendance records.
     *
     * @param  array<int>  $ids
     * @param  AttendanceStatusEnum  $status
     * @return int
     */
    public function bulkUpdateStatus(array $ids, AttendanceStatusEnum $status): int
    {
        return Attendance::query()->whereIn('id', $ids)->update(['status' => $status->value]);
    }

    /**
     * Import multiple attendance records from an uploaded file.
     *
     * @param  UploadedFile  $file
     * @return void
     */
    public function importAttendances(UploadedFile $file): void
    {
        ExcelFacade::import(new AttendancesImport, $file);
    }

    /**
     * Download an attendances CSV template.
     *
     * @return string
     * @throws RuntimeException
     */
    public function download(): string
    {
        $fileName = 'attendances-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template attendances not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing attendance data.
     *
     * @param  array<int>  $ids
     * @param  string  $format
     * @param  array<string>  $columns
     * @param  array{start_date?: string, end_date?: string}  $filters
     * @return string
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'attendances_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new AttendancesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }

    /**
     * Handle a real-time punch from a physical clock-in device (ADMS).
     * Automatically pairs check-ins and check-outs, and calculates late status.
     *
     * @param array<string, mixed> $data Contains staff_id, timestamp, and device_id.
     * @return array{attendance: Attendance, type: string}|null Returns the Attendance record and punch type, or null if employee not found.
     */
    public function handleDevicePunch(array $data): ?array
    {
        return DB::transaction(function () use ($data) {
            // Match the device's User PIN with your system's staff_id
            $employee = Employee::query()->where('staff_id', $data['staff_id'])->first();

            if (!$employee) {
                Log::warning("Device Punch: Employee with staff ID {$data['staff_id']} not found.");
                return null;
            }

            $punchTimestamp = Carbon::parse($data['timestamp']);
            $date = $punchTimestamp->format('Y-m-d');
            $time = $punchTimestamp->format('H:i:s');

            $attendance = Attendance::query()
                ->where('date', $date)
                ->where('employee_id', $employee->id)
                ->first();

            // Scenario 1: First punch of the day -> Record as Check-in
            if (!$attendance) {
                $hrmSetting = DB::table('hrm_settings')->latest()->first();
                $expectedCheckin = Carbon::parse($hrmSetting ? $hrmSetting->checkin : '08:00:00');

                $status = $punchTimestamp->format('H:i:s') <= $expectedCheckin->format('H:i:s')
                    ? AttendanceStatusEnum::PRESENT->value
                    : AttendanceStatusEnum::LATE->value;

                $attendance = Attendance::query()->create([
                    'date' => $date,
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id ?? 1, // Fallback to system admin if employee lacks a user login
                    'checkin' => $time,
                    'checkout' => null,
                    'status' => $status,
                    'note' => 'Punched via Device: ' . ($data['device_id'] ?? 'Unknown'),
                ]);

                return ['attendance' => $attendance->fresh(['employee']), 'type' => 'Check-in'];
            }

            // Scenario 2: Subsequent punch of the day -> Record/Update as Check-out
            // Only update if the new punch is strictly after the checkin time (prevents duplicate rapid punches)
            if (Carbon::parse($time)->gt(Carbon::parse($attendance->checkin))) {
                $attendance->update([
                    'checkout' => $time,
                    'note' => ltrim($attendance->note . ' | Checkout via Device: ' . ($data['device_id'] ?? 'Unknown'), ' | '),
                ]);

                return ['attendance' => $attendance->fresh(['employee']), 'type' => 'Check-out'];
            }

            return null; // Punch ignored (likely a duplicate rapid punch)
        });
    }
}
