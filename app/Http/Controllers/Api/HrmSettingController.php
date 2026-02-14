<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\HrmSettingRequest;
use App\Http\Resources\HrmSettingResource;
use App\Services\HrmSettingService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for HRM Setting.
 *
 * Handles human resource management configuration (checkin/checkout).
 *
 * @group HRM Setting
 */
class HrmSettingController extends Controller
{
    /**
     * HrmSettingController constructor.
     *
     * @param HrmSettingService $service
     */
    public function __construct(
        private readonly HrmSettingService $service
    )
    {
    }

    /**
     * Display the HRM setting.
     *
     * @return JsonResponse The HRM setting or empty if not configured.
     */
    public function show(): JsonResponse
    {
        $setting = $this->service->getHrmSetting();

        if (!$setting) {
            return response()->success(null, 'No HRM setting configured');
        }

        return response()->success(
            new HrmSettingResource($setting),
            'HRM setting retrieved successfully'
        );
    }

    /**
     * Update the HRM setting.
     *
     * @param HrmSettingRequest $request Validated HRM configuration.
     * @return JsonResponse The updated HRM setting.
     */
    public function update(HrmSettingRequest $request): JsonResponse
    {
        $setting = $this->service->updateHrmSetting($request->validated());

        return response()->success(
            new HrmSettingResource($setting),
            'HRM setting updated successfully'
        );
    }
}
