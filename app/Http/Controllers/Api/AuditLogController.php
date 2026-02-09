<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditIndexRequest;
use App\Http\Resources\AuditResource;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use OwenIt\Auditing\Models\Audit;

/**
 * API Controller for Audit Log.
 *
 * Handles index (paginated listing) of audits from Laravel Auditing.
 * Returns full Audit structure per standard.
 * Uses through() to preserve LengthAwarePaginator so the success macro
 * outputs data and meta at top level (matches other controllers).
 *
 * @group Audit Log
 */
class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $service
    ) {}

    /**
     * Display a paginated listing of audits.
     *
     * @param  AuditIndexRequest  $request  Validated query params: per_page, page, search, event, auditable_type, ip_address, user.
     * @return JsonResponse Paginated audits with meta and links.
     */
    public function index(AuditIndexRequest $request): JsonResponse
    {
        $audits = $this->service->getAudits(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        // Transform data while preserving LengthAwarePaginator for the Response Macro
        $audits->through(fn (Audit $audit) => new AuditResource($audit));

        return response()->success(
            $audits,
            'Audits fetched successfully'
        );
    }
}
