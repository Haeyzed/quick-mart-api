<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PosSettingRequest;
use App\Http\Resources\PosSettingResource;
use App\Services\PosSettingService;
use Illuminate\Http\JsonResponse;

class PosSettingController extends Controller
{
    public function __construct(
        private readonly PosSettingService $service
    )
    {
    }

    public function show(): JsonResponse
    {
        $setting = $this->service->getPosSetting();

        return response()->success(
            new PosSettingResource($setting),
            'POS setting retrieved successfully'
        );
    }

    /**
     * Update the POS setting.
     *
     * @param PosSettingRequest $request Validated POS configuration.
     * @return JsonResponse The updated POS setting.
     */
    public function update(PosSettingRequest $request): JsonResponse
    {
        $setting = $this->service->updatePosSetting($request->validated());

        return response()->success(
            new PosSettingResource($setting),
            'POS setting updated successfully'
        );
    }
}
