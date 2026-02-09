<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UtilityService;
use Illuminate\Http\JsonResponse;

/**
 * Utility controller for miscellaneous endpoints.
 *
 * @group Utility
 */
class UtilityController extends Controller
{
    public function __construct(
        private readonly UtilityService $service
    ) {}

    /**
     * Get all distinct auditable model types (for filters/combobox).
     *
     * @return JsonResponse
     */
    public function auditableModels(): JsonResponse
    {
        $models = $this->service->getAuditableModels();

        return response()->success(
            $models,
            'Auditable models fetched successfully'
        );
    }
}
