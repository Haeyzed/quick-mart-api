<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GeneralSetting;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

/**
 * Service class for General Setting operations.
 *
 * Centralizes business logic for retrieving and updating general application settings.
 * Handles site logo and favicon uploads using brands-style approach (path + URL).
 */
class GeneralSettingService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * GeneralSettingService constructor.
     *
     * @param  UploadService  $uploadService  Handles file uploads for site logo and favicon.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Retrieve the general setting (singleton).
     *
     * Requires general_setting permission.
     */
    public function getGeneralSetting(): GeneralSetting
    {
        $this->requirePermission('general_setting');

        return GeneralSetting::latest()->firstOrFail();
    }

    /**
     * Update the general setting.
     *
     * Requires general_setting permission.
     *
     * @param  array<string, mixed>  $data  Validated data.
     * @param  UploadedFile|null  $siteLogo  Optional logo file.
     * @param  UploadedFile|null  $favicon  Optional favicon file.
     * @param  string|null  $clientIp  Optional client IP for maintenance_allowed_ips.
     * @return GeneralSetting The updated general setting instance.
     */
    public function updateGeneralSetting(
        array $data,
        ?UploadedFile $siteLogo = null,
        ?UploadedFile $favicon = null,
        ?string $clientIp = null
    ): GeneralSetting {
        $this->requirePermission('general_setting');

        $setting = GeneralSetting::latest()->firstOrFail();

        $data = $this->normalizeGeneralSettingData($data, $clientIp);

        if ($siteLogo) {
            if ($setting->site_logo) {
                $this->uploadService->delete($setting->site_logo);
            }
            $data['site_logo'] = $this->uploadService->upload($siteLogo, 'logo');
            $data['site_logo_url'] = $this->uploadService->url($data['site_logo']);
        }

        if ($favicon) {
            if ($setting->favicon) {
                $this->uploadService->delete($setting->favicon);
            }
            $data['favicon'] = $this->uploadService->upload($favicon, 'logo');
            $data['favicon_url'] = $this->uploadService->url($data['favicon']);
        }

        $setting->update($data);

        Cache::forget('general_setting');

        return $setting->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeGeneralSettingData(array $data, ?string $clientIp = null): array
    {
        $toBoolean = fn ($val) => filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (array_key_exists('is_rtl', $data)) {
            $data['is_rtl'] = $toBoolean($data['is_rtl']);
        }
        if (array_key_exists('is_zatca', $data)) {
            $data['is_zatca'] = $toBoolean($data['is_zatca']);
        }
        if (array_key_exists('without_stock', $data)) {
            $data['without_stock'] = in_array($data['without_stock'], ['yes', true, '1', 1], true) ? 'yes' : 'no';
        }
        if (array_key_exists('is_packing_slip', $data)) {
            $data['is_packing_slip'] = $toBoolean($data['is_packing_slip']);
        }
        if (array_key_exists('disable_signup', $data)) {
            $data['disable_signup'] = $toBoolean($data['disable_signup']);
        }
        if (array_key_exists('disable_forgot_password', $data)) {
            $data['disable_forgot_password'] = $toBoolean($data['disable_forgot_password']);
        }
        if (array_key_exists('show_products_details_in_sales_table', $data)) {
            $data['show_products_details_in_sales_table'] = $toBoolean($data['show_products_details_in_sales_table']);
        }
        if (array_key_exists('show_products_details_in_purchase_table', $data)) {
            $data['show_products_details_in_purchase_table'] = $toBoolean($data['show_products_details_in_purchase_table']);
        }
        if (array_key_exists('decimal', $data)) {
            $data['decimal'] = (int) $data['decimal'];
        }
        if (array_key_exists('state', $data)) {
            $data['state'] = $data['state'] !== null ? (int) $data['state'] : null;
        }
        if (array_key_exists('expiry_alert_days', $data)) {
            $data['expiry_alert_days'] = (int) ($data['expiry_alert_days'] ?? 0);
        }
        if (array_key_exists('margin_type', $data)) {
            $data['margin_type'] = (int) $data['margin_type'];
        }
        if (array_key_exists('maintenance_allowed_ips', $data)) {
            $data['maintenance_allowed_ips'] = $this->normalizeMaintenanceAllowedIps(
                $data['maintenance_allowed_ips'],
                $clientIp
            );
        }

        unset($data['site_logo'], $data['favicon']);

        return $data;
    }

    /**
     * Normalize maintenance_allowed_ips: add current IP if not in list when enabled.
     *
     * @param  string|null  $ips  Comma-separated IPs or null when disabled.
     * @param  string|null  $clientIp  Current request IP.
     * @return string|null Normalized comma-separated IPs or null when disabled.
     */
    private function normalizeMaintenanceAllowedIps(?string $ips, ?string $clientIp): ?string
    {
        if (empty(trim((string) $ips))) {
            return null;
        }

        $userIps = array_filter(array_map('trim', explode(',', $ips)));

        if ($clientIp !== null && ! in_array($clientIp, $userIps, true)) {
            $userIps[] = $clientIp;
        }

        return implode(',', array_unique($userIps));
    }
}
