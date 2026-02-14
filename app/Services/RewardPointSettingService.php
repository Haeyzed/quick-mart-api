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
     */
    public function __construct()
    {
    }

    /**
     * Retrieve the reward point setting (singleton).
     *
     * Requires reward_point_setting permission.
     *
     * @return RewardPointSetting|null The latest reward point setting or null if not configured.
     */
    public function getRewardPointSetting(): ?RewardPointSetting
    {
        $this->requirePermission('reward_point_setting');

        return RewardPointSetting::latest()->first();
    }

    /**
     * Update or create the reward point setting.
     *
     * Requires reward_point_setting permission.
     * If no setting exists, creates one (same as quick-mart-old).
     *
     * @param array<string, mixed> $data Validated data.
     * @return RewardPointSetting The updated or created reward point setting instance.
     */
    public function updateRewardPointSetting(array $data): RewardPointSetting
    {
        $this->requirePermission('reward_point_setting');

        $setting = RewardPointSetting::latest()->first();

        if ($setting) {
            $setting->update($data);

            return $setting->fresh();
        }

        return RewardPointSetting::create($data);
    }
}
