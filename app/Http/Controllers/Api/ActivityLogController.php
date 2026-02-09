<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityLogIndexRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Activity Log.
 *
 * Handles index (paginated listing) of activity logs.
 * Role-based: users with role_id > 2 only see their own logs.
 *
 * @group Activity Log
 */
class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $service
    ) {}

    /**
     * Display a paginated listing of activity logs.
     *
     * @param ActivityLogIndexRequest $request Validated query params: per_page, page, search.
     * @return JsonResponse Paginated activity logs with meta and links.
     */
    public function index(ActivityLogIndexRequest $request): JsonResponse
    {
        $activityLogs = $this->service->getActivityLogs(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $activityLogs->through(fn (ActivityLog $log) => new ActivityLogResource($log));

        return response()->success(
            $activityLogs,
            'Activity logs fetched successfully'
        );
    }
}
