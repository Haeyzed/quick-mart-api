<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatusEnum;
use App\Exports\AttendancesExport;
use App\Imports\AttendancesImport;
use App\Models\Attendance;
use Exception;
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
     * Integrates HrmSetting defaults if times are omitted, and auto-calculates statuses.
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

            // 1. Fetch Global HRM Settings
            $hrmSetting = DB::table('hrm_settings')->latest()->first();
            $defaultCheckin = $hrmSetting ? $hrmSetting->checkin : '08:00:00';
            $defaultCheckout = $hrmSetting ? $hrmSetting->checkout : '17:00:00';

            // 2. Set actual Check-in / Check-out times (Fallback to HRM Defaults if omitted)
            $actualCheckin = !empty($data['checkin']) ? Carbon::parse($data['checkin'])->format('H:i:s') : $defaultCheckin;
            $actualCheckout = !empty($data['checkout']) ? Carbon::parse($data['checkout'])->format('H:i:s') : null;

            // 3. Auto-calculate status if omitted
            $status = $data['status'] ?? null;
            if (!$status) {
                $status = Carbon::parse($actualCheckin)->lte(Carbon::parse($defaultCheckin))
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
                        'checkin' => $actualCheckin,
                        'checkout' => $actualCheckout,
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
     *
     * @param array<string, mixed> $data Contains staff_id, timestamp, and device_id.
     * @return array{attendance: Attendance, type: string}|null Returns the Attendance record and punch type, or null if ignored.
     */
    public function handleDevicePunch(array $data): ?array
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::query()->where('staff_id', $data['staff_id'])->first();

            if (!$employee) {
                Log::warning("Device Punch: Employee with staff ID {$data['staff_id']} not found.");
                return null;
            }

            return $this->processPunch(
                $employee,
                Carbon::parse($data['timestamp']),
                'Device: ' . ($data['device_id'] ?? 'Unknown')
            );
        });
    }

    /**
     * Handle a manual clock-in/out punch from the Web Dashboard.
     *
     * @param int $userId The ID of the authenticated user pushing the button.
     * @param string|null $ipAddress The IP address of the user for auditing.
     * @return array{attendance: Attendance, type: string}|null
     * @throws Exception If the user does not have an attached employee profile.
     */
    public function handleWebPunch(int $userId, ?string $ipAddress = null): ?array
    {
        return DB::transaction(function () use ($userId, $ipAddress) {
            $employee = Employee::query()->where('user_id', $userId)->first();

            if (!$employee) {
                throw new Exception("No employee profile is associated with your account.");
            }

            $source = 'Web Portal' . ($ipAddress ? " (IP: {$ipAddress})" : '');

            return $this->processPunch(
                $employee,
                now(),
                $source
            );
        });
    }

    /**
     * Core shared logic to process a punch (check-in/check-out) for a specific employee.
     * Automatically pairs check-ins and check-outs, calculates late status,
     * and strictly enforces that check-outs only register ON or AFTER the official HRM setting time.
     *
     * @param Employee $employee
     * @param Carbon $punchTimestamp
     * @param string $source
     * @return array{attendance: Attendance, type: string}|null
     */
    private function processPunch(Employee $employee, Carbon $punchTimestamp, string $source): ?array
    {
        $date = $punchTimestamp->format('Y-m-d');
        $time = $punchTimestamp->format('H:i:s');

        // Fetch Global HRM Settings for strict calculations
        $hrmSetting = DB::table('hrm_settings')->latest()->first();
        $defaultCheckin = $hrmSetting ? $hrmSetting->checkin : '08:00:00';
        $defaultCheckout = $hrmSetting ? $hrmSetting->checkout : '17:00:00';

        $attendance = Attendance::query()
            ->where('date', $date)
            ->where('employee_id', $employee->id)
            ->first();

        // ==========================================
        // SCENARIO 1: First punch of the day (Check-in)
        // ==========================================
        if (!$attendance) {
            $status = $punchTimestamp->format('H:i:s') <= Carbon::parse($defaultCheckin)->format('H:i:s')
                ? AttendanceStatusEnum::PRESENT->value
                : AttendanceStatusEnum::LATE->value;

            $attendance = Attendance::query()->create([
                'date' => $date,
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id ?? 1,
                'checkin' => $time,
                'checkout' => null,
                'status' => $status,
                'note' => "Check-in via {$source}",
            ]);

            return ['attendance' => $attendance->fresh(['employee']), 'type' => 'Check-in'];
        }

        // ==========================================
        // SCENARIO 2: Subsequent punch (Check-out)
        // ==========================================
        $newPunchTime = Carbon::parse($time);
        $officialCheckoutTime = Carbon::parse($defaultCheckout);

        // STRICT RULE: Only consider as Check-out if time is ON or AFTER the official checkout time
        if ($newPunchTime->greaterThanOrEqualTo($officialCheckoutTime)) {

            $attendance->update([
                'checkout' => $time,
                'note' => ltrim($attendance->note . " | Check-out via {$source}", ' | '),
            ]);

            return ['attendance' => $attendance->fresh(['employee']), 'type' => 'Check-out'];

        } else {
            // Early punch attempt log (e.g. employee trying to leave at 2 PM)
            $checkinTime = Carbon::parse($attendance->checkin);

            // 15-minute anti-double-punch buffer
            if ($newPunchTime->diffInMinutes($checkinTime) >= 15) {
                $attendance->update([
                    'note' => ltrim($attendance->note . " | Early punch attempt via {$source} at {$time}", ' | '),
                ]);
            }
        }

        // Ignored punch (either too early to leave, or a double-punch)
        return null;
    }
}
