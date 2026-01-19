<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * GeneralSetting Model
 *
 * Represents the general application settings (singleton pattern).
 *
 * @property int $id
 * @property string|null $site_title
 * @property string|null $site_logo
 * @property bool $is_rtl
 * @property string|null $currency
 * @property string|null $currency_position
 * @property string|null $staff_access
 * @property bool $without_stock
 * @property bool $is_packing_slip
 * @property string|null $date_format
 * @property string|null $theme
 * @property string|null $modules
 * @property string|null $developed_by
 * @property string|null $phone
 * @property string|null $email
 * @property int|null $free_trial_limit
 * @property int|null $package_id
 * @property string|null $invoice_format
 * @property int|null $decimal
 * @property string|null $state
 * @property Carbon|null $expiry_date
 * @property string|null $expiry_type
 * @property int|null $expiry_value
 * @property string|null $subscription_type
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $active_payment_gateway
 * @property string|null $stripe_public_key
 * @property string|null $stripe_secret_key
 * @property string|null $paypal_client_id
 * @property string|null $paypal_client_secret
 * @property string|null $razorpay_number
 * @property string|null $razorpay_key
 * @property string|null $razorpay_secret
 * @property bool $is_zatca
 * @property string|null $company_name
 * @property string|null $vat_registration_number
 * @property string|null $dedicated_ip
 * @property string|null $paystack_public_key
 * @property string|null $paystack_secret_key
 * @property string|null $paydunya_master_key
 * @property string|null $paydunya_public_key
 * @property string|null $paydunya_secret_key
 * @property string|null $paydunya_token
 * @property string|null $ssl_store_id
 * @property string|null $ssl_store_password
 * @property string|null $app_key
 * @property bool|null $show_products_details_in_sales_table
 * @property bool|null $show_products_details_in_purchase_table
 * @property string|null $timezone
 * @property string|null $font_css
 * @property string|null $pos_css
 * @property string|null $auth_css
 * @property string|null $custom_css
 * @property bool|null $disable_signup
 * @property bool|null $disable_forgot_password
 * @property string|null $favicon
 * @property int|null $expiry_alert_days
     * @property string|null $margin_type
     * @property string|null $storage_provider
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     */
class GeneralSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_title',
        'site_logo',
        'is_rtl',
        'currency',
        'currency_position',
        'staff_access',
        'without_stock',
        'is_packing_slip',
        'date_format',
        'theme',
        'modules',
        'developed_by',
        'phone',
        'email',
        'free_trial_limit',
        'package_id',
        'invoice_format',
        'decimal',
        'state',
        'expiry_date',
        'expiry_type',
        'expiry_value',
        'subscription_type',
        'meta_title',
        'meta_description',
        'active_payment_gateway',
        'stripe_public_key',
        'stripe_secret_key',
        'paypal_client_id',
        'paypal_client_secret',
        'razorpay_number',
        'razorpay_key',
        'razorpay_secret',
        'is_zatca',
        'company_name',
        'vat_registration_number',
        'dedicated_ip',
        'paystack_public_key',
        'paystack_secret_key',
        'paydunya_master_key',
        'paydunya_public_key',
        'paydunya_secret_key',
        'paydunya_token',
        'ssl_store_id',
        'ssl_store_password',
        'app_key',
        'show_products_details_in_sales_table',
        'show_products_details_in_purchase_table',
        'timezone',
        'font_css',
        'pos_css',
        'auth_css',
        'custom_css',
        'disable_signup',
        'disable_forgot_password',
        'favicon',
            'expiry_alert_days',
            'margin_type',
            'storage_provider',
        ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_rtl' => 'boolean',
            'without_stock' => 'boolean',
            'is_packing_slip' => 'boolean',
            'free_trial_limit' => 'integer',
            'package_id' => 'integer',
            'decimal' => 'integer',
            'expiry_value' => 'integer',
            'is_zatca' => 'boolean',
            'show_products_details_in_sales_table' => 'boolean',
            'show_products_details_in_purchase_table' => 'boolean',
            'disable_signup' => 'boolean',
            'disable_forgot_password' => 'boolean',
            'expiry_alert_days' => 'integer',
            'expiry_date' => 'date',
        ];
    }
}

