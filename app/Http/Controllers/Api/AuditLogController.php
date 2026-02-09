<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditIndexRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Resources\AuditResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Models\Audit;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Audit Log.
 *
 * Handles index (paginated listing) and export of audits from Laravel Auditing.
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

        $audits->through(fn (Audit $audit) => new AuditResource($audit));

        return response()->success(
            $audits,
            'Audits fetched successfully'
        );
    }

    /**
     * Export audits to Excel or PDF.
     *
     * @param  ExportRequest  $request  Validated: ids, format, method, columns, user_id (if email).
     * @return JsonResponse|BinaryFileResponse
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();

        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportAudits(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return response()->download(
                Storage::disk('public')->path($filePath)
            );
        }

        return response()->success(null, 'Export processed and sent via email');
    }
}
