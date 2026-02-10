<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\RewardPointSettingRequest;
use App\Http\Resources\RewardPointSettingResource;
use App\Services\RewardPointSettingService;
use Illuminate\Http\JsonResponse;

class RewardPointSettingController extends Controller
{
    public function __construct(
        private readonly RewardPointSettingService $service
    ) {}

    public function show(): JsonResponse
    {
        $setting = $this->service->getRewardPointSetting();

        if (! $setting) {
            return response()->success(null, 'No reward point setting configured');
        }

        return response()->success(
            new RewardPointSettingResource($setting),
            'Reward point setting retrieved successfully'
        );
    }

    /**
     * Update the reward point setting.
     *
     * @param  RewardPointSettingRequest  $request  Validated reward point configuration.
     * @return JsonResponse The updated reward point setting.
     */
    public function update(RewardPointSettingRequest $request): JsonResponse
    {
        $setting = $this->service->updateRewardPointSetting($request->validated());

        return response()->success(
            new RewardPointSettingResource($setting),
            'Reward point setting updated successfully'
        );
    }
}
