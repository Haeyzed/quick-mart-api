<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GeneralSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Class GeneralSettingService
 *
 * Handles all core business logic and database interactions for General Settings.
 * Acts as the intermediary between the controllers and the database layer.
 */
class GeneralSettingService extends BaseService
{
    /**
     * GeneralSettingService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Retrieve the general setting (singleton).
     *
     * @return GeneralSetting The latest general setting instance.
     */
    public function getGeneralSetting(): GeneralSetting
    {
        return GeneralSetting::latest()->firstOrFail();
    }

    /**
     * Update the general setting.
     *
     * Updates the general setting record and handles file management within a database transaction.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @param  UploadedFile|null  $siteLogo  Optional logo file.
     * @param  UploadedFile|null  $favicon  Optional favicon file.
     * @param  string|null  $clientIp  Optional client IP for maintenance list.
     * @return GeneralSetting The freshly updated GeneralSetting model instance.
     */
    public function updateGeneralSetting(
        array $data,
        ?UploadedFile $siteLogo = null,
        ?UploadedFile $favicon = null,
        ?string $clientIp = null
    ): GeneralSetting {
        return DB::transaction(function () use ($data, $siteLogo, $favicon, $clientIp) {
            $setting = GeneralSetting::latest()->firstOrFail();
            $data = $this->normalizeData($data, $clientIp);

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
        });
    }

    /**
     * Normalize general setting data.
     *
     * @param  array<string, mixed>  $data
     * @param  string|null  $clientIp
     * @return array<string, mixed>
     */
    private function normalizeData(array $data, ?string $clientIp): array
    {
        $toBool = fn($val) => filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $boolKeys = [
            'is_rtl', 'is_zatca', 'is_packing_slip', 'disable_signup',
            'disable_forgot_password', 'show_products_details_in_sales_table',
            'show_products_details_in_purchase_table'
        ];

        foreach ($boolKeys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = $toBool($data[$key]);
            }
        }

        if (isset($data['without_stock'])) {
            $data['without_stock'] = in_array($data['without_stock'], ['yes', true, '1', 1], true) ? 'yes' : 'no';
        }

        if (isset($data['maintenance_allowed_ips'])) {
            $ips = array_filter(array_map('trim', explode(',', (string)$data['maintenance_allowed_ips'])));
            if ($clientIp && !in_array($clientIp, $ips, true)) {
                $ips[] = $clientIp;
            }
            $data['maintenance_allowed_ips'] = empty($ips) ? null : implode(',', array_unique($ips));
        }

        unset($data['site_logo'], $data['favicon']);
        return $data;
    }
}
