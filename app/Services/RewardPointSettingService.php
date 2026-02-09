<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RewardPointSetting;
use App\Traits\CheckPermissionsTrait;

/**
 * Service class for Reward Point Setting operations.
 *
 * Centralizes business logic for reward points configuration.
 */
class RewardPointSettingService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * RewardPointSettingService constructor.
     *
     * @param ActivityLogService $activityLogService Handles activity logging for audit trail.
     */
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Retrieve the reward point setting (singleton).
     *
     * Requires reward_point_setting permission.
     *
     * @return RewardPointSetting The latest reward point setting instance.
     */
    public function getRewardPointSetting(): RewardPointSetting
    {
        $this->requirePermission('reward_point_setting');

        return RewardPointSetting::latest()->firstOrFail();
    }

    /**
     * Update the reward point setting.
     *
     * Requires reward_point_setting permission.
     *
     * @param array<string, mixed> $data Validated data.
     * @return RewardPointSetting The updated reward point setting instance.
     */
    public function updateRewardPointSetting(array $data): RewardPointSetting
    {
        $this->requirePermission('reward_point_setting');

        $setting = RewardPointSetting::latest()->firstOrFail();
        $setting->update($data);

        $this->activityLogService->log(
            'Updated Reward Point Setting',
            (string) $setting->id,
            'Reward point configuration was updated.'
        );

        return $setting->fresh();
    }
}
