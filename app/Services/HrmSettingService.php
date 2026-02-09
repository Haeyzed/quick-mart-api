<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HrmSetting;
use App\Traits\CheckPermissionsTrait;

/**
 * Service class for HRM Setting operations.
 *
 * Centralizes business logic for human resource management configuration.
 */
class HrmSettingService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * HrmSettingService constructor.
     *
     * @param ActivityLogService $activityLogService Handles activity logging for audit trail.
     */
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Retrieve the HRM setting (singleton).
     *
     * Requires hrm_setting permission.
     *
     * @return HrmSetting|null The latest HRM setting or null if not configured.
     */
    public function getHrmSetting(): ?HrmSetting
    {
        $this->requirePermission('hrm_setting');
        return HrmSetting::latest()->first();
    }

    /**
     * Update the HRM setting.
     *
     * Requires hrm_setting permission.
     *
     * @param array<string, mixed> $data Validated data.
     * @return HrmSetting The updated HRM setting instance.
     */
    public function updateHrmSetting(array $data): HrmSetting
    {
        $this->requirePermission('hrm_setting');
        $setting = HrmSetting::latest()->first();
        if (! $setting) {
            $setting = new HrmSetting;
        }
        $setting->fill($data)->save();

        $this->activityLogService->log(
            'Updated HRM Setting',
            (string) $setting->id,
            'HRM configuration was updated.'
        );

        return $setting->fresh();
    }
}
