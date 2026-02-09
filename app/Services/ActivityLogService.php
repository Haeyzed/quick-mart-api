<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\CheckPermissionsTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;

/**
 * Service class for Audit Log operations.
 *
 * Uses Laravel Auditing (owen-it/laravel-auditing) as the source.
 * Returns raw Audit records without transformation.
 * Users with role_id > 2 only see their own audits.
 */
class ActivityLogService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * Retrieve audits with optional filters and pagination.
     *
     * Role-based: users with role_id > 2 only see their own audits.
     * Requires activity-log-index permission.
     *
     * @param  array<string, mixed>  $filters  Associative array with optional keys: search, event, auditable_type.
     * @param  int  $perPage  Number of items per page.
     * @return LengthAwarePaginator<Audit>
     */
    public function getAudits(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('activity-log-index');

        $user = Auth::user();

        $query = Audit::query()
            ->with('user')
            ->when($user && $user->role_id > 2, fn ($q) => $q->where('audits.user_id', $user->id))
            ->when(! empty($filters['search'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn ($subQ) => $subQ
                    ->where('audits.event', 'like', $term)
                    ->orWhere('audits.auditable_type', 'like', $term)
                    ->orWhere('audits.auditable_id', 'like', $term)
                    ->orWhere('audits.tags', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                );
            })
            ->when(! empty($filters['event'] ?? null), fn ($q) => $q->where('audits.event', $filters['event']))
            ->when(! empty($filters['auditable_type'] ?? null), fn ($q) => $q->where('audits.auditable_type', $filters['auditable_type']))
            ->orderByDesc('audits.id');

        return $query->paginate($perPage);
    }
}
