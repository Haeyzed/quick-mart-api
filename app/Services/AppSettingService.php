<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\MobileToken;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Support\Facades\Config;

/**
 * Service class for App Setting operations.
 *
 * Centralizes business logic for mobile app connection settings and device tokens.
 */
class AppSettingService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * Retrieve app setting (install URL, app key, QR payload) and active tokens.
     *
     * Auto-generates app_key if empty. Requires general_setting permission.
     */
    public function getAppSetting(): array
    {
        $this->requirePermission('general_setting');

        $generalSetting = GeneralSetting::latest()->first();

        if (! $generalSetting) {
            $generalSetting = GeneralSetting::create(['app_key' => (string) random_int(100000, 999999)]);
        }

        if (empty($generalSetting->app_key)) {
            $generalSetting->update(['app_key' => (string) random_int(100000, 999999)]);
        }

        $installUrl = rtrim(Config::get('app.url'), '/');
        $qrCodePayload = $installUrl . '?app_key=' . $generalSetting->app_key;
        $mobileTokens = MobileToken::active()->orderByDesc('last_active')->get();

        return [
            'install_url' => $installUrl,
            'app_key' => $generalSetting->app_key,
            'qr_code_payload' => $qrCodePayload,
            'mobile_tokens' => $mobileTokens,
        ];
    }

    /**
     * Deactivate (revoke) a mobile token by ID.
     *
     * Requires general_setting permission.
     */
    public function deleteToken(int $id): void
    {
        $this->requirePermission('general_setting');

        $token = MobileToken::findOrFail($id);
        $token->update(['is_active' => false]);
    }
}
