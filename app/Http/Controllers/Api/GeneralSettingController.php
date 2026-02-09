<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\GeneralSettingRequest;
use App\Http\Resources\GeneralSettingResource;
use App\Models\GeneralSetting;
use App\Services\GeneralSettingService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for General Setting.
 *
 * Handles show and update of general application settings.
 *
 * @group General Setting
 */
class GeneralSettingController extends Controller
{
    /**
     * GeneralSettingController constructor.
     *
     * @param GeneralSettingService $service
     */
    public function __construct(
        private readonly GeneralSettingService $service
    ) {}

    /**
     * Display the general setting.
     *
     * @return JsonResponse The general setting.
     */
    public function show(): JsonResponse
    {
        $setting = $this->service->getGeneralSetting();

        return response()->success(
            new GeneralSettingResource($setting),
            'General setting retrieved successfully'
        );
    }

    /**
     * Update the general setting.
     *
     * @param GeneralSettingRequest $request Validated general setting data.
     * @return JsonResponse The updated general setting.
     */
    public function update(GeneralSettingRequest $request): JsonResponse
    {
        $setting = $this->service->updateGeneralSetting(
            $request->validated(),
            $request->file('site_logo'),
            $request->file('favicon')
        );

        return response()->success(
            new GeneralSettingResource($setting),
            'General setting updated successfully'
        );
    }
}
