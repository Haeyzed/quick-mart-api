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
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class HolidayService
 *
 * Handles all core business logic and database interactions for Holidays (leave requests).
 * Acts as the intermediary between the controllers and the database layer.
 */
class HolidayService
{
    use MailInfo;

    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated holidays based on provided filters.
     *
     * Retrieves a paginated list of holidays, applying scopes for user, approval status,
     * search (note), and date ranges.
     *
     * @param  array<string, mixed>  $filters  An associative array of filters (e.g., 'search', 'user_id', 'is_approved', 'start_date', 'end_date').
     * @param  int  $perPage  The number of records to return per page.
     * @return LengthAwarePaginator A paginated collection of Holiday models.
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
     * Create a newly registered holiday (leave request).
     *
     * Sets user_id from authenticated user if not provided, and is_approved based on permission.
     * Stores the new holiday within a database transaction.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new holiday.
     * @return Holiday The newly created Holiday model instance.
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
     * Update an existing holiday's information.
     *
     * Updates the holiday record within a database transaction.
     *
     * @param  Holiday  $holiday  The holiday model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Holiday The freshly updated Holiday model instance.
     */
    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $holiday->update($data);

            return $holiday->fresh();
        });
    }

    /**
     * Delete a specific holiday.
     *
     * @param  Holiday  $holiday  The holiday model instance to delete.
     */
    public function deleteHoliday(Holiday $holiday): void
    {
        DB::transaction(function () use ($holiday) {
            $holiday->delete();
        });
    }

    /**
     * Bulk delete multiple holidays.
     *
     * Iterates over an array of holiday IDs and deletes each record.
     *
     * @param  array<int>  $ids  Array of holiday IDs to be deleted.
     * @return int The total count of successfully deleted holidays.
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
     * Approve a holiday request and send approval email to the user.
     *
     * @param  Holiday  $holiday  The holiday model instance to approve.
     * @return Holiday The freshly updated Holiday model instance with user loaded.
     *
     * @throws ValidationException If mail settings are not configured.
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
     * Get user holidays for a specific month (calendar view).
     *
     * Returns an array keyed by day number with holiday details or false, plus metadata for navigation.
     *
     * @param  int  $userId  The user ID to fetch holidays for.
     * @param  int  $year  The year.
     * @param  int  $month  The month (1–12).
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
     * Import multiple holidays from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file containing holiday data.
     */
    public function importHolidays(UploadedFile $file): void
    {
        ExcelFacade::import(new HolidaysImport, $file);
    }

    /**
     * Retrieve the path to the sample holidays import template.
     *
     * @return string The absolute file path to the sample CSV.
     *
     * @throws RuntimeException If the template file does not exist on the server.
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
     * Generate an export file (Excel or PDF) containing holiday data.
     *
     * Compiles the requested holiday data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param  array<int>  $ids  Specific holiday IDs to export (leave empty to export all based on filters).
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include in the export.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
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
