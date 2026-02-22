<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\GeneralSettingRequest;
use App\Http\Resources\GeneralSettingResource;
use App\Services\GeneralSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Class GeneralSettingController
 *
 * API Controller for General Setting.
 * Handles authorization via Policy and delegates logic to GeneralSettingService.
 *
 * @tags General Setting
 */
class GeneralSettingController extends Controller
{
    /**
     * GeneralSettingController constructor.
     */
    public function __construct(
        private readonly GeneralSettingService $service
    ) {}

    /**
     * Display the general setting.
     *
     * Retrieve the global application settings.
     */
    public function show(): JsonResponse
    {
        if (auth()->user()->denies('manage general settings')) {
            return response()->forbidden('Permission denied for viewing general settings.');
        }

        return response()->success(
            new GeneralSettingResource($this->service->getGeneralSetting()),
            'General setting retrieved successfully'
        );
    }

    /**
     * Update the general setting.
     *
     * Update the specified general settings including logo and favicon uploads.
     */
    public function update(GeneralSettingRequest $request): JsonResponse
    {
        if (auth()->user()->denies('manage general settings')) {
            return response()->forbidden('Permission denied for updating general settings.');
        }

        $setting = $this->service->updateGeneralSetting(
            $request->validated(),
            $request->file('site_logo'),
            $request->file('favicon'),
            $request->ip()
        );

        return response()->success(
            new GeneralSettingResource($setting),
            'General setting updated successfully'
        );
    }
}
