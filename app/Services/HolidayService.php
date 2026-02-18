<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\HolidaysExport;
use App\Imports\HolidaysImport;
use App\Mail\HolidayApprove;
use App\Models\GeneralSetting;
use App\Models\Holiday;
use App\Models\MailSetting;
use App\Traits\MailInfo;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;
use Illuminate\Validation\ValidationException;

/**
 * Class HolidayService
 *
 * Handles business logic for Holidays (leave requests).
 */
class HolidayService
{
    use MailInfo;

    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated holidays based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedHolidays(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Holiday::query()
            ->with('user')
            ->filter($filters)
            ->latest('from_date')
            ->paginate($perPage);
    }

    /**
     * Create a new holiday.
     *
     * @param array<string, mixed> $data
     */
    public function createHoliday(array $data): Holiday
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['user_id'])) {
                $data['user_id'] = Auth::id();
            }
            if (! isset($data['is_approved'])) {
                $user = Auth::user();
                $data['is_approved'] = $user && $user->can('approve holidays');
            }
            return Holiday::query()->create($data);
        });
    }

    /**
     * Update an existing holiday.
     *
     * @param array<string, mixed> $data
     */
    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $holiday->update($data);
            return $holiday->fresh();
        });
    }

    /**
     * Delete a holiday.
     */
    public function deleteHoliday(Holiday $holiday): void
    {
        DB::transaction(function () use ($holiday) {
            $holiday->delete();
        });
    }

    /**
     * Bulk delete holidays.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteHolidays(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $holidays = Holiday::query()->whereIn('id', $ids)->get();
            $count = 0;
            foreach ($holidays as $holiday) {
                $holiday->delete();
                $count++;
            }
            return $count;
        });
    }

    /**
     * Approve a holiday and send email.
     */
    public function approveHoliday(Holiday $holiday): Holiday
    {
        return DB::transaction(function () use ($holiday) {
            $holiday->update(['is_approved' => true]);
            $holiday->load('user');
            if ($holiday->user && $holiday->user->email) {
                $mailSetting = MailSetting::default()->first();
                if (! $mailSetting) {
                    throw ValidationException::withMessages([
                        'email' => ['Mail settings are not configured. Please contact the administrator.'],
                    ]);
                }
                try {
                    $this->setMailInfo($mailSetting);
                    $generalSetting = GeneralSetting::query()->latest()->first();
                    Mail::to($holiday->user->email)->send(new HolidayApprove($holiday, $generalSetting));
                } catch (Exception $e) {
                    report($e);
                }
            }
            return $holiday->fresh();
        });
    }

    /**
     * Get user holidays for a specific month.
     *
     * @return array{holidays: array<int, mixed>, metadata: array<string, mixed>}
     */
    public function getUserHolidaysByMonth(int $userId, int $year, int $month): array
    {
        $holidays = [];
        $numberOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $generalSetting = GeneralSetting::query()->latest()->first();
        $dateFormat = $generalSetting?->date_format ?? 'Y-m-d';
        $firstDayOfMonth = sprintf('%d-%02d-%02d', $year, $month, 1);

        for ($day = 1; $day <= $numberOfDays; $day++) {
            $date = sprintf('%d-%02d-%02d', $year, $month, $day);
            $holiday = Holiday::query()
                ->whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->where('is_approved', true)
                ->where('user_id', $userId)
                ->first();

            if ($holiday) {
                $fromDateFormatted = $holiday->from_date->format($dateFormat);
                $toDateFormatted = $holiday->to_date->format($dateFormat);
                $holidays[$day] = [
                    'id' => $holiday->id,
                    'from_date' => $holiday->from_date->format('Y-m-d'),
                    'to_date' => $holiday->to_date->format('Y-m-d'),
                    'formatted_period' => $fromDateFormatted.' To '.$toDateFormatted,
                    'note' => $holiday->note,
                ];
            } else {
                $holidays[$day] = false;
            }
        }

        $startDay = (int) date('w', strtotime($firstDayOfMonth)) + 1;
        $prevDate = strtotime('-1 month', strtotime($firstDayOfMonth));
        $nextDate = strtotime('+1 month', strtotime($firstDayOfMonth));

        return [
            'holidays' => $holidays,
            'metadata' => [
                'year' => $year,
                'month' => $month,
                'number_of_days' => $numberOfDays,
                'start_day' => $startDay,
                'prev_year' => (int) date('Y', $prevDate),
                'prev_month' => (int) date('m', $prevDate),
                'next_year' => (int) date('Y', $nextDate),
                'next_month' => (int) date('m', $nextDate),
            ],
        ];
    }

    /**
     * Import holidays from file.
     */
    public function importHolidays(UploadedFile $file): void
    {
        ExcelFacade::import(new HolidaysImport, $file);
    }

    /**
     * Download holidays CSV template.
     */
    public function download(): string
    {
        $fileName = 'holidays-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);
        if (! File::exists($path)) {
            throw new RuntimeException('Holidays import template not found.');
        }
        return $path;
    }

    /**
     * Generate holidays export file.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'holidays_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;
        ExcelFacade::store(
            new HolidaysExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );
        return $relativePath;
    }
}
