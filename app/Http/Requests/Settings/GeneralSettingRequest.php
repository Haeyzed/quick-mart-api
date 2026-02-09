<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for general setting update validation.
 *
 * Validates site_title, site_logo, favicon, currency, date_format, and other general settings.
 */
class GeneralSettingRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'max:255'],
            'site_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
            'is_rtl' => ['nullable', 'boolean'],
            'is_zatca' => ['nullable', 'boolean'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'vat_registration_number' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:255'],
            'currency_position' => ['nullable', Rule::in(['prefix', 'suffix'])],
            'decimal' => ['nullable', 'integer', 'min:0', 'max:6'],
            'staff_access' => ['nullable', Rule::in(['all', 'own', 'warehouse'])],
            'without_stock' => ['nullable', Rule::in(['yes', 'no'])],
            'is_packing_slip' => ['nullable', 'boolean'],
            'date_format' => ['nullable', 'string', 'max:50'],
            'developed_by' => ['nullable', 'string', 'max:255'],
            'invoice_format' => ['nullable', Rule::in(['standard', 'gst'])],
            'state' => ['nullable', 'integer', 'in:1,2'],
            'default_margin_value' => ['nullable', 'numeric', 'min:0'],
            'font_css' => ['nullable', 'string'],
            'pos_css' => ['nullable', 'string'],
            'auth_css' => ['nullable', 'string'],
            'custom_css' => ['nullable', 'string'],
            'expiry_alert_days' => ['nullable', 'integer', 'min:0'],
            'disable_signup' => ['nullable', 'boolean'],
            'disable_forgot_password' => ['nullable', 'boolean'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'show_products_details_in_sales_table' => ['nullable', 'boolean'],
            'show_products_details_in_purchase_table' => ['nullable', 'boolean'],
        ];
    }
}
