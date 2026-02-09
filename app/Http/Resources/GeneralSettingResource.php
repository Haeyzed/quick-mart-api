<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for GeneralSetting entity.
 *
 * Transforms GeneralSetting model into a consistent JSON structure for API responses.
 * Uses stored site_logo_url/favicon_url when available (brands-style); falls back to building URL from path.
 *
 * @mixin \App\Models\GeneralSetting
 */
class GeneralSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array<string, mixed> The transformed general setting data for API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_title' => $this->site_title,
            'site_logo' => $this->site_logo,
            'site_logo_url' => $this->site_logo_url,
            'favicon' => $this->favicon,
            'favicon_url' => $this->favicon_url,
            'is_rtl' => $this->is_rtl,
            'is_zatca' => $this->is_zatca,
            'company_name' => $this->company_name,
            'vat_registration_number' => $this->vat_registration_number,
            'currency' => $this->currency,
            'currency_position' => $this->currency_position,
            'decimal' => $this->decimal,
            'staff_access' => $this->staff_access,
            'without_stock' => $this->without_stock,
            'is_packing_slip' => $this->is_packing_slip,
            'date_format' => $this->date_format,
            'developed_by' => $this->developed_by,
            'invoice_format' => $this->invoice_format,
            'state' => $this->state,
            'default_margin_value' => $this->default_margin_value !== null ? (float) $this->default_margin_value : null,
            'font_css' => $this->font_css,
            'pos_css' => $this->pos_css,
            'auth_css' => $this->auth_css,
            'custom_css' => $this->custom_css,
            'expiry_alert_days' => $this->expiry_alert_days,
            'disable_signup' => $this->disable_signup,
            'disable_forgot_password' => $this->disable_forgot_password,
            'maintenance_allowed_ips' => $this->maintenance_allowed_ips,
            'margin_type' => $this->margin_type !== null ? (int) $this->margin_type : null,
            'timezone' => $this->timezone,
            'show_products_details_in_sales_table' => $this->show_products_details_in_sales_table,
            'show_products_details_in_purchase_table' => $this->show_products_details_in_purchase_table,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
