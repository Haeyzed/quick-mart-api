<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Service class for Activity Log operations.
 *
 * Centralizes business logic for retrieving and creating activity logs.
 * Per quick-mart-old: users with role_id > 2 only see their own logs.
 */
class ActivityLogService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * Create an activity log entry.
     *
     * Persists user action into activity_logs table for audit trail.
     *
     * @param string $action Action description (e.g. 'Updated General Setting', 'Updated Mail Setting').
     * @param string|null $referenceNo Optional reference identifier (e.g. invoice ref, setting name).
     * @param string|null $itemDescription Optional additional context.
     * @param int|null $userId User who performed the action; defaults to authenticated user.
     * @return ActivityLog The created activity log instance.
     */
    public function log(
        string $action,
        ?string $referenceNo = null,
        ?string $itemDescription = null,
        ?int $userId = null
    ): ActivityLog {
        $user = Auth::user();
        $userId = $userId ?? $user?->id;

        if ($userId === null) {
            throw new \RuntimeException('Activity log requires a user. No authenticated user and no user_id provided.');
        }

        return ActivityLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'reference_no' => $referenceNo,
            'date' => now()->toDateString(),
            'item_description' => $itemDescription,
        ]);
    }

    /**
     * Retrieve activity logs with optional filters and pagination.
     *
     * Role-based: users with role_id > 2 only see their own activity logs.
     * Requires activity-log-index permission.
     *
     * @param array<string, mixed> $filters Associative array with optional keys: search.
     * @param int $perPage Number of items per page.
     * @return LengthAwarePaginator Paginated activity log collection.
     */
    public function getActivityLogs(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('activity-log-index');

        $user = Auth::user();

        return ActivityLog::query()
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.name as user_name')
            ->when($user && $user->role_id > 2, fn ($q) => $q->where('activity_logs.user_id', $user->id))
            ->when(! empty($filters['search'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn ($subQ) => $subQ
                    ->where('activity_logs.action', 'like', $term)
                    ->orWhere('activity_logs.reference_no', 'like', $term)
                    ->orWhere('users.name', 'like', $term)
                );
            })
            ->orderByDesc('activity_logs.id')
            ->paginate($perPage);
    }
}
