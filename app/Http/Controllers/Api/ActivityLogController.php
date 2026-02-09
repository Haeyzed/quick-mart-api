<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditIndexRequest;
use App\Http\Resources\AuditResource;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Audit Log.
 *
 * Handles index (paginated listing) of audits from Laravel Auditing.
 * Returns full Audit structure per standard.
 *
 * @group Audit Log
 */
class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $service
    ) {}

    /**
     * Display a paginated listing of audits.
     *
     * @param  AuditIndexRequest  $request  Validated query params: per_page, page, search, event, auditable_type.
     * @return JsonResponse Paginated audits with meta and links.
     */
    public function index(AuditIndexRequest $request): JsonResponse
    {
        $audits = $this->service->getAudits(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            AuditResource::collection($audits),
            'Audits fetched successfully'
        );
    }
}
