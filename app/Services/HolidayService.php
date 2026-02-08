<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\HolidayApprove;
use App\Models\GeneralSetting;
use App\Models\Holiday;
use App\Models\MailSetting;
use App\Traits\MailInfo;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * HolidayService
 *
 * Handles all business logic for holiday operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class HolidayService extends BaseService
{
    use MailInfo;

    /**
     * Get paginated list of holidays with optional filters.
     * If user doesn't have 'holiday' permission, automatically filters to their own holidays.
     *
     * @param array<string, mixed> $filters Available filters: user_id, is_approved, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Holiday>
     */
    public function getHolidays(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $user = Auth::user();

        // If user doesn't have 'holiday' permission, only show their own holidays
        if ($user && $user->role_id) {
            $role = Role::find($user->role_id);
            if (!$role || !$role->hasPermissionTo('holiday')) {
                // User doesn't have permission, only show their holidays
                $filters['user_id'] = $user->id;
            }
        } elseif ($user) {
            // No role_id, default to showing only their holidays
            $filters['user_id'] = $user->id;
        }

        return Holiday::query()
            ->when(
                isset($filters['user_id']),
                fn($query) => $query->where('user_id', $filters['user_id'])
            )
            ->when(
                isset($filters['is_approved']),
                fn($query) => $query->where('is_approved', (bool)$filters['is_approved'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where('note', 'like', '%' . $filters['search'] . '%')
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single holiday by ID.
     *
     * @param int $id Holiday ID
     * @return Holiday
     */
    public function getHoliday(int $id): Holiday
    {
        return Holiday::findOrFail($id);
    }

    /**
     * Create a new holiday.
     *
     * @param array<string, mixed> $data Validated holiday data
     * @return Holiday
     */
    public function createHoliday(array $data): Holiday
    {
        return $this->transaction(function () use ($data) {
            // Auto-set user_id if not provided (use authenticated user)
            if (!isset($data['user_id'])) {
                $data['user_id'] = Auth::id();
            }

            // Auto-set is_approved based on role permission (matching old controller logic)
            if (!isset($data['is_approved'])) {
                $user = Auth::user();
                if ($user && $user->role_id) {
                    $role = Role::find($user->role_id);
                    if ($role && $role->hasPermissionTo('holiday')) {
                        $data['is_approved'] = true;
                    } else {
                        $data['is_approved'] = false;
                    }
                } else {
                    $data['is_approved'] = false;
                }
            }

            // Normalize data to match database schema
            $data = $this->normalizeHolidayData($data);

            return Holiday::create($data);
        });
    }

    /**
     * Normalize holiday data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeHolidayData(array $data): array
    {
        // is_approved is stored as boolean (true/false)
        if (!isset($data['is_approved'])) {
            $data['is_approved'] = false;
        } else {
            $data['is_approved'] = (bool)filter_var(
                $data['is_approved'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        // recurring is stored as boolean (true/false)
        if (!isset($data['recurring'])) {
            $data['recurring'] = false;
        } else {
            $data['recurring'] = (bool)filter_var(
                $data['recurring'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $data;
    }

    /**
     * Update an existing holiday.
     *
     * @param Holiday $holiday Holiday instance to update
     * @param array<string, mixed> $data Validated holiday data
     * @return Holiday
     */
    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        return $this->transaction(function () use ($holiday, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeHolidayData($data);
            $holiday->update($data);
            return $holiday->fresh();
        });
    }

    /**
     * Bulk delete multiple holidays.
     *
     * @param array<int> $ids Array of holiday IDs to delete
     * @return int Number of holidays deleted
     */
    public function bulkDeleteHolidays(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $holiday = Holiday::findOrFail($id);
                $this->deleteHoliday($holiday);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete holiday {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single holiday.
     *
     * @param Holiday $holiday Holiday instance to delete
     * @return bool
     */
    public function deleteHoliday(Holiday $holiday): bool
    {
        return $this->transaction(function () use ($holiday) {
            return $holiday->delete();
        });
    }

    /**
     * Approve a holiday.
     *
     * @param Holiday $holiday Holiday instance to approve
     * @return Holiday
     */
    public function approveHoliday(Holiday $holiday): Holiday
    {
        return $this->transaction(function () use ($holiday) {
            $holiday->is_approved = true;
            $holiday->save();

            // Load user relationship
            $holiday->load('user');

            // Send approval email
            if ($holiday->user && $holiday->user->email) {
                $mailSetting = MailSetting::default()->first();
                if (!$mailSetting) {
                    throw ValidationException::withMessages([
                        'email' => ['Mail settings are not configured. Please contact the administrator.'],
                    ]);
                }

                try {
                    $this->setMailInfo($mailSetting);
                    $generalSetting = GeneralSetting::latest()->first();
                    Mail::to($holiday->user->email)->send(new HolidayApprove($holiday, $generalSetting));
                } catch (Exception $e) {
                    // Log error but don't fail the approval
                    $this->logError("Failed to send holiday approval email: " . $e->getMessage());
                }
            }

            return $holiday->fresh();
        });
    }

    /**
     * Get user holidays for a specific month.
     *
     * @param int $userId User ID
     * @param int $year Year (e.g., 2024)
     * @param int $month Month (1-12)
     * @return array<string, mixed> Array containing holidays data and calendar metadata
     */
    public function getUserHolidaysByMonth(int $userId, int $year, int $month): array
    {
        $holidays = [];
        $numberOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $generalSetting = GeneralSetting::latest()->first();
        $dateFormat = $generalSetting?->date_format ?? 'Y-m-d';

        // Build first day of month for date calculations
        $firstDayOfMonth = sprintf('%d-%02d-%02d', $year, $month, 1);

        for ($day = 1; $day <= $numberOfDays; $day++) {
            $date = sprintf('%d-%02d-%02d', $year, $month, $day);

            $holiday = Holiday::whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->where('is_approved', true)
                ->where('user_id', $userId)
                ->first();

            if ($holiday) {
                $fromDateFormatted = $holiday->from_date->format($dateFormat);
                $toDateFormatted = $holiday->to_date->format($dateFormat);

                // Return structured data for API (matching old format but with more details)
                $holidays[$day] = [
                    'id' => $holiday->id,
                    'from_date' => $holiday->from_date->format('Y-m-d'),
                    'to_date' => $holiday->to_date->format('Y-m-d'),
                    'formatted_period' => $fromDateFormatted . ' To ' . $toDateFormatted,
                    'note' => $holiday->note,
                ];
            } else {
                $holidays[$day] = false;
            }
        }

        // Calculate calendar metadata (matching old controller logic)
        $startDay = (int)date('w', strtotime($firstDayOfMonth)) + 1; // Day of week (1=Monday, 7=Sunday)
        $prevDate = strtotime('-1 month', strtotime($firstDayOfMonth));
        $nextDate = strtotime('+1 month', strtotime($firstDayOfMonth));

        return [
            'holidays' => $holidays,
            'metadata' => [
                'year' => $year,
                'month' => $month,
                'number_of_days' => $numberOfDays,
                'start_day' => $startDay, // Day of week the month starts (1=Monday, 7=Sunday)
                'prev_year' => (int)date('Y', $prevDate),
                'prev_month' => (int)date('m', $prevDate),
                'next_year' => (int)date('Y', $nextDate),
                'next_month' => (int)date('m', $nextDate),
            ],
        ];
    }
}

