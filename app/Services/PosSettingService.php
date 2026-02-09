<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PosSetting;
use App\Traits\CheckPermissionsTrait;

/**
 * Service class for POS Setting operations.
 *
 * Centralizes business logic for point-of-sale configuration.
 */
class PosSettingService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * PosSettingService constructor.
     */
    public function __construct() {}

    /**
     * Retrieve the POS setting (singleton).
     *
     * Requires pos_setting permission.
     *
     * @return PosSetting The latest POS setting instance.
     */
    public function getPosSetting(): PosSetting
    {
        $this->requirePermission('pos_setting');

        return PosSetting::latest()->firstOrFail();
    }

    /**
     * Update the POS setting.
     *
     * Requires pos_setting permission.
     *
     * @param  array<string, mixed>  $data  Validated data.
     * @return PosSetting The updated POS setting instance.
     */
    public function updatePosSetting(array $data): PosSetting
    {
        $this->requirePermission('pos_setting');

        $setting = PosSetting::latest()->firstOrFail();
        $data = $this->normalizeData($data);

        if (isset($data['payment_options']) && is_array($data['payment_options'])) {
            $data['payment_options'] = implode(',', $data['payment_options']);
        }

        if (! empty($data['stripe_secret_key'])) {
            $data['stripe_secret_key'] = trim($data['stripe_secret_key']);
        } else {
            unset($data['stripe_secret_key']);
        }
        if (! empty($data['paypal_live_api_password'])) {
            $data['paypal_live_api_password'] = trim($data['paypal_live_api_password']);
        } else {
            unset($data['paypal_live_api_password']);
        }
        if (! empty($data['paypal_live_api_secret'])) {
            $data['paypal_live_api_secret'] = trim($data['paypal_live_api_secret']);
        } else {
            unset($data['paypal_live_api_secret']);
        }

        $setting->update($data);

        return $setting->fresh();
    }

    /**
     * Normalize and cast incoming data.
     *
     * @param  array<string, mixed>  $data  Raw input data.
     * @return array<string, mixed> Normalized data.
     */
    private function normalizeData(array $data): array
    {
        $toBool = fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (array_key_exists('keybord_active', $data)) {
            $data['keybord_active'] = $toBool($data['keybord_active']);
        }
        if (array_key_exists('is_table', $data)) {
            $data['is_table'] = $toBool($data['is_table']);
        }
        if (array_key_exists('show_print_invoice', $data)) {
            $data['show_print_invoice'] = $toBool($data['show_print_invoice']);
        }
        if (array_key_exists('send_sms', $data)) {
            $data['send_sms'] = $toBool($data['send_sms']);
        }
        if (array_key_exists('cash_register', $data)) {
            $data['cash_register'] = $toBool($data['cash_register']);
        }
        if (array_key_exists('product_number', $data)) {
            $data['product_number'] = (int) $data['product_number'];
        }

        return $data;
    }
}
