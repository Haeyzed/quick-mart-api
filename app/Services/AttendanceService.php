<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatusEnum;
use App\Exports\AttendancesExport;
use App\Imports\AttendancesImport;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\GeneralSetting;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class AttendanceService
 *
 * Handles all core business logic and database interactions for Attendances.
 * Acts as the intermediary between the controllers and the database layer.
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
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedAttendances(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $generalSetting = DB::table('general_settings')->latest()->first();

        $query = Attendance::query()
            ->with(['employee:id,name', 'user:id,name,warehouse_id'])
            ->filter($filters)
            ->orderByDesc('date');

        if (Auth::check() &&
            !Auth::user()->hasRole('Super Admin') &&
            $generalSetting?->staff_access === 'own'
        ) {
            $query->where('user_id', Auth::id());
        }

        $paginator = $query->paginate($perPage);
        $groupedCollection = $paginator->getCollection()->groupBy(['date', 'employee_id']);

        return $paginator->setCollection($groupedCollection);
    }

    /**
     * Create a newly registered attendance.
     *
     * @param array<string, mixed> $data
     * @return Collection
     */
    public function createAttendance(array $data): Collection
    {
        return DB::transaction(function () use ($data) {
            $setting = DB::table('hrm_settings')->latest()->first();
            $checkinStandard = $setting->checkin ?? '08:00:00';

            $createdAttendances = collect();

            $date = Carbon::parse($data['date'])->format('Y-m-d');

            foreach ($data['employee_ids'] as $employeeId) {
                $existing = Attendance::query()
                    ->where('date', $date)
                    ->where('employee_id', $employeeId)
                    ->first();

                if ($existing) {
                    throw new Exception(
                        "Attendance record already exists for Employee ID {$employeeId} on {$date}. Please update the existing record instead of creating a duplicate."
                    );
                }

                $status = $data['status'] ?? (
                strtotime($checkinStandard) >= strtotime($data['checkin'])
                    ? AttendanceStatusEnum::PRESENT->value
                    : AttendanceStatusEnum::LATE->value
                );

                if ($status instanceof AttendanceStatusEnum) {
                    $status = $status->value;
                }

                $attendance = Attendance::create([
                    'date' => $date,
                    'employee_id' => $employeeId,
                    'user_id' => Auth::id(),
                    'checkin' => Carbon::parse($data['checkin'])->format('H:i:s'),
                    'checkout' => !empty($data['checkout']) ? Carbon::parse($data['checkout'])->format('H:i:s') : null,
                    'status' => $status,
                    'note' => $data['note'] ?? null,
                ]);

                $createdAttendances->push($attendance);
            }

            return $createdAttendances;
        });
    }

    /**
     * Update an existing attendance record manually via HR.
     *
     * @param Attendance $attendance
     * @param array<string, mixed> $data
     * @return Attendance
     */
    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        return DB::transaction(function () use ($attendance, $data) {
            if (array_key_exists('checkin', $data) && empty($data['status'])) {
                $setting = DB::table('hrm_settings')->latest()->first();
                $expectedCheckin = Carbon::parse($setting->checkin ?? '08:00:00');
                $actualCheckin = Carbon::parse($data['checkin']);

                $data['status'] = $actualCheckin->format('H:i:s') <= $expectedCheckin->format('H:i:s')
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
                Log::warning("Device Punch Failed: Employee with staff ID {$data['staff_id']} not found.");
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
                throw new Exception("No employee profile is associated with your account. Unable to log attendance.");
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
     * Tracks check-ins, early check-outs, and handles double-punch debouncing.
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

        $hrmSetting = DB::table('hrm_settings')->latest()->first();
        $defaultCheckin = Carbon::parse($hrmSetting->checkin ?? '08:00:00');
        $defaultCheckout = Carbon::parse($hrmSetting->checkout ?? '17:00:00');

        $attendance = Attendance::query()
            ->where('date', $date)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$attendance) {
            $status = $time <= $defaultCheckin->format('H:i:s')
                ? AttendanceStatusEnum::PRESENT->value
                : AttendanceStatusEnum::LATE->value;

            $attendance = Attendance::query()->create([
                'date' => $date,
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id ?? Auth::id() ?? 1,
                'checkin' => $time,
                'checkout' => null,
                'status' => $status,
                'note' => "Check-in via {$source}",
            ]);

            return ['attendance' => $attendance->fresh(['employee']), 'type' => 'Check-in'];
        }

        $newPunchTime = Carbon::parse($time);
        $checkinTime = Carbon::parse($attendance->checkin);

        if ($newPunchTime->diffInMinutes($checkinTime) < 15) {
            return null;
        }

        $isEarly = $time < $defaultCheckout->format('H:i:s');
        $noteStatus = $isEarly ? "Early Check-out" : "Check-out";

        $existingNote = $attendance->note ? $attendance->note . " | " : "";

        $attendance->update([
            'checkout' => $time,
            'note' => $existingNote . "{$noteStatus} via {$source} at {$time}",
        ]);

        return ['attendance' => $attendance->fresh(['employee']), 'type' => 'Check-out'];
    }

    /**
     * Delete an attendance record.
     *
     * @param Attendance $attendance
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
     * @param array<int> $ids
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
     * @param array<int> $ids
     * @param AttendanceStatusEnum $status
     * @return int
     */
    public function bulkUpdateStatus(array $ids, AttendanceStatusEnum $status): int
    {
        return Attendance::query()->whereIn('id', $ids)->update(['status' => $status->value]);
    }

    /**
     * Import multiple attendance records from an uploaded file.
     *
     * @param UploadedFile $file
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
        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException('Template attendances not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing attendance data.
     *
     * @param array<int> $ids
     * @param string $format
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters
     * @return string
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'attendances_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new AttendancesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
