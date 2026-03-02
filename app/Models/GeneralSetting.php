<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class GeneralSetting
 *
 * Represents the general application settings (singleton pattern). Handles the underlying data
 * structure, relationships, and specific query scopes for general setting entities.
 *
 * @property int $id
 * @property string|null $site_title
 * @property string|null $site_logo
 * @property string|null $site_logo_url
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
 * @property string|null $maintenance_allowed_ips
 * @property string|null $favicon
 * @property string|null $favicon_url
 * @property int|null $expiry_alert_days
 * @property string|null $margin_type
 * @property string|null $storage_provider
 * @property string|null $google_client_id
 * @property string|null $google_client_secret
 * @property string|null $google_redirect_url
 * @property bool $google_login_enabled
 * @property string|null $facebook_client_id
 * @property string|null $facebook_client_secret
 * @property string|null $facebook_redirect_url
 * @property bool $facebook_login_enabled
 * @property string|null $github_client_id
 * @property string|null $github_client_secret
 * @property string|null $github_redirect_url
 * @property bool $github_login_enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $token
 * @property float $default_margin_value
 * @property string|null $cloudinary_cloud_name
 * @property string|null $cloudinary_api_key
 * @property string|null $cloudinary_api_secret
 * @property string|null $cloudinary_secure_url
 * @property string|null $aws_access_key_id
 * @property string|null $aws_secret_access_key
 * @property string|null $aws_default_region
 * @property string|null $aws_bucket
 * @property string|null $aws_url
 * @property string|null $aws_endpoint
 * @property bool $aws_use_path_style_endpoint
 * @property string|null $sftp_host
 * @property string|null $sftp_username
 * @property string|null $sftp_password
 * @property string|null $sftp_private_key
 * @property string|null $sftp_passphrase
 * @property int $sftp_port
 * @property string $sftp_root
 * @property string|null $ftp_host
 * @property string|null $ftp_username
 * @property string|null $ftp_password
 * @property int $ftp_port
 * @property string $ftp_root
 * @property bool $ftp_passive
 * @property bool $ftp_ssl
 *
 * @method static Builder|GeneralSetting newModelQuery()
 * @method static Builder|GeneralSetting newQuery()
 * @method static Builder|GeneralSetting query()
 * @method static Builder|GeneralSetting filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|GeneralSetting customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting last30Days(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting last7Days(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting lastYear(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting monthToDate(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting today(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting whereAppKey($value)
 * @method static Builder<static>|GeneralSetting whereAuthCss($value)
 * @method static Builder<static>|GeneralSetting whereAwsAccessKeyId($value)
 * @method static Builder<static>|GeneralSetting whereAwsBucket($value)
 * @method static Builder<static>|GeneralSetting whereAwsDefaultRegion($value)
 * @method static Builder<static>|GeneralSetting whereAwsEndpoint($value)
 * @method static Builder<static>|GeneralSetting whereAwsSecretAccessKey($value)
 * @method static Builder<static>|GeneralSetting whereAwsUrl($value)
 * @method static Builder<static>|GeneralSetting whereAwsUsePathStyleEndpoint($value)
 * @method static Builder<static>|GeneralSetting whereCloudinaryApiKey($value)
 * @method static Builder<static>|GeneralSetting whereCloudinaryApiSecret($value)
 * @method static Builder<static>|GeneralSetting whereCloudinaryCloudName($value)
 * @method static Builder<static>|GeneralSetting whereCloudinarySecureUrl($value)
 * @method static Builder<static>|GeneralSetting whereCompanyName($value)
 * @method static Builder<static>|GeneralSetting whereCreatedAt($value)
 * @method static Builder<static>|GeneralSetting whereCurrency($value)
 * @method static Builder<static>|GeneralSetting whereCurrencyPosition($value)
 * @method static Builder<static>|GeneralSetting whereCustomCss($value)
 * @method static Builder<static>|GeneralSetting whereDateFormat($value)
 * @method static Builder<static>|GeneralSetting whereDecimal($value)
 * @method static Builder<static>|GeneralSetting whereDefaultMarginValue($value)
 * @method static Builder<static>|GeneralSetting whereDevelopedBy($value)
 * @method static Builder<static>|GeneralSetting whereDisableForgotPassword($value)
 * @method static Builder<static>|GeneralSetting whereDisableSignup($value)
 * @method static Builder<static>|GeneralSetting whereExpiryAlertDays($value)
 * @method static Builder<static>|GeneralSetting whereExpiryDate($value)
 * @method static Builder<static>|GeneralSetting whereExpiryType($value)
 * @method static Builder<static>|GeneralSetting whereExpiryValue($value)
 * @method static Builder<static>|GeneralSetting whereFacebookClientId($value)
 * @method static Builder<static>|GeneralSetting whereFacebookClientSecret($value)
 * @method static Builder<static>|GeneralSetting whereFacebookLoginEnabled($value)
 * @method static Builder<static>|GeneralSetting whereFacebookRedirectUrl($value)
 * @method static Builder<static>|GeneralSetting whereFavicon($value)
 * @method static Builder<static>|GeneralSetting whereFaviconUrl($value)
 * @method static Builder<static>|GeneralSetting whereFontCss($value)
 * @method static Builder<static>|GeneralSetting whereFtpHost($value)
 * @method static Builder<static>|GeneralSetting whereFtpPassive($value)
 * @method static Builder<static>|GeneralSetting whereFtpPassword($value)
 * @method static Builder<static>|GeneralSetting whereFtpPort($value)
 * @method static Builder<static>|GeneralSetting whereFtpRoot($value)
 * @method static Builder<static>|GeneralSetting whereFtpSsl($value)
 * @method static Builder<static>|GeneralSetting whereFtpUsername($value)
 * @method static Builder<static>|GeneralSetting whereGithubClientId($value)
 * @method static Builder<static>|GeneralSetting whereGithubClientSecret($value)
 * @method static Builder<static>|GeneralSetting whereGithubLoginEnabled($value)
 * @method static Builder<static>|GeneralSetting whereGithubRedirectUrl($value)
 * @method static Builder<static>|GeneralSetting whereGoogleClientId($value)
 * @method static Builder<static>|GeneralSetting whereGoogleClientSecret($value)
 * @method static Builder<static>|GeneralSetting whereGoogleLoginEnabled($value)
 * @method static Builder<static>|GeneralSetting whereGoogleRedirectUrl($value)
 * @method static Builder<static>|GeneralSetting whereId($value)
 * @method static Builder<static>|GeneralSetting whereInvoiceFormat($value)
 * @method static Builder<static>|GeneralSetting whereIsPackingSlip($value)
 * @method static Builder<static>|GeneralSetting whereIsRtl($value)
 * @method static Builder<static>|GeneralSetting whereIsZatca($value)
 * @method static Builder<static>|GeneralSetting whereMaintenanceAllowedIps($value)
 * @method static Builder<static>|GeneralSetting whereMarginType($value)
 * @method static Builder<static>|GeneralSetting whereModules($value)
 * @method static Builder<static>|GeneralSetting wherePackageId($value)
 * @method static Builder<static>|GeneralSetting wherePosCss($value)
 * @method static Builder<static>|GeneralSetting whereSftpHost($value)
 * @method static Builder<static>|GeneralSetting whereSftpPassphrase($value)
 * @method static Builder<static>|GeneralSetting whereSftpPassword($value)
 * @method static Builder<static>|GeneralSetting whereSftpPort($value)
 * @method static Builder<static>|GeneralSetting whereSftpPrivateKey($value)
 * @method static Builder<static>|GeneralSetting whereSftpRoot($value)
 * @method static Builder<static>|GeneralSetting whereSftpUsername($value)
 * @method static Builder<static>|GeneralSetting whereShowProductsDetailsInPurchaseTable($value)
 * @method static Builder<static>|GeneralSetting whereShowProductsDetailsInSalesTable($value)
 * @method static Builder<static>|GeneralSetting whereSiteLogo($value)
 * @method static Builder<static>|GeneralSetting whereSiteLogoUrl($value)
 * @method static Builder<static>|GeneralSetting whereSiteTitle($value)
 * @method static Builder<static>|GeneralSetting whereStaffAccess($value)
 * @method static Builder<static>|GeneralSetting whereState($value)
 * @method static Builder<static>|GeneralSetting whereStorageProvider($value)
 * @method static Builder<static>|GeneralSetting whereSubscriptionType($value)
 * @method static Builder<static>|GeneralSetting whereTheme($value)
 * @method static Builder<static>|GeneralSetting whereTimezone($value)
 * @method static Builder<static>|GeneralSetting whereToken($value)
 * @method static Builder<static>|GeneralSetting whereUpdatedAt($value)
 * @method static Builder<static>|GeneralSetting whereVatRegistrationNumber($value)
 * @method static Builder<static>|GeneralSetting whereWithoutStock($value)
 * @method static Builder<static>|GeneralSetting yearToDate(string $column = 'created_at')
 * @method static Builder<static>|GeneralSetting yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class GeneralSetting extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_title',
        'site_logo',
        'site_logo_url',
        'favicon',
        'favicon_url',
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
        'maintenance_allowed_ips',
        'expiry_alert_days',
        'default_margin_value',
        'margin_type',
        'storage_provider',
        'google_client_id',
        'google_client_secret',
        'google_redirect_url',
        'google_login_enabled',
        'facebook_client_id',
        'facebook_client_secret',
        'facebook_redirect_url',
        'facebook_login_enabled',
        'github_client_id',
        'github_client_secret',
        'github_redirect_url',
        'github_login_enabled',
        // Cloudinary v3 credentials
        'cloudinary_cloud_name',
        'cloudinary_api_key',
        'cloudinary_api_secret',
        'cloudinary_secure_url',
        // AWS S3 credentials
        'aws_access_key_id',
        'aws_secret_access_key',
        'aws_default_region',
        'aws_bucket',
        'aws_url',
        'aws_endpoint',
        'aws_use_path_style_endpoint',
        // SFTP credentials
        'sftp_host',
        'sftp_username',
        'sftp_password',
        'sftp_private_key',
        'sftp_passphrase',
        'sftp_port',
        'sftp_root',
        // FTP credentials
        'ftp_host',
        'ftp_username',
        'ftp_password',
        'ftp_port',
        'ftp_root',
        'ftp_passive',
        'ftp_ssl',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
        'google_login_enabled' => 'boolean',
        'facebook_login_enabled' => 'boolean',
        'github_login_enabled' => 'boolean',
        'aws_use_path_style_endpoint' => 'boolean',
        'ftp_passive' => 'boolean',
        'ftp_ssl' => 'boolean',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->customRange(
            ! empty($filters['start_date']) ? $filters['start_date'] : null,
            ! empty($filters['end_date']) ? $filters['end_date'] : null,
        );
    }
}
