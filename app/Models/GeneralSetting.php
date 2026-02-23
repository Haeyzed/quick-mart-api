<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * GeneralSetting Model
 * 
 * Represents the general application settings (singleton pattern).
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
 * @property numeric $default_margin_value
 * @property string|null $cloudinary_cloud_name Cloudinary Cloud Name
 * @property string|null $cloudinary_api_key Cloudinary API Key
 * @property string|null $cloudinary_api_secret Cloudinary API Secret
 * @property string|null $cloudinary_secure_url Cloudinary Secure URL (optional)
 * @property string|null $aws_access_key_id AWS Access Key ID
 * @property string|null $aws_secret_access_key AWS Secret Access Key
 * @property string|null $aws_default_region AWS Default Region
 * @property string|null $aws_bucket AWS S3 Bucket Name
 * @property string|null $aws_url AWS S3 URL (optional)
 * @property string|null $aws_endpoint AWS S3 Endpoint (optional, for custom S3-compatible services)
 * @property bool $aws_use_path_style_endpoint Use path-style endpoint for S3
 * @property string|null $sftp_host SFTP Host
 * @property string|null $sftp_username SFTP Username
 * @property string|null $sftp_password SFTP Password
 * @property string|null $sftp_private_key SFTP Private Key (optional, for key-based authentication)
 * @property string|null $sftp_passphrase SFTP Passphrase (optional, for encrypted private keys)
 * @property int $sftp_port SFTP Port
 * @property string $sftp_root SFTP Root Directory
 * @property string|null $ftp_host FTP Host
 * @property string|null $ftp_username FTP Username
 * @property string|null $ftp_password FTP Password
 * @property int $ftp_port FTP Port
 * @property string $ftp_root FTP Root Directory
 * @property bool $ftp_passive FTP Passive Mode
 * @property bool $ftp_ssl FTP SSL/TLS
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAppKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAuthCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsAccessKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsBucket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsDefaultRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsSecretAccessKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsUsePathStyleEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinaryApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinaryApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinaryCloudName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinarySecureUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCurrencyPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCustomCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDecimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDefaultMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDevelopedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDisableForgotPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDisableSignup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryAlertDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookLoginEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFaviconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFontCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpPassive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpSsl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubLoginEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleLoginEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereInvoiceFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereIsPackingSlip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereIsRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereIsZatca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereMaintenanceAllowedIps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereMarginType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereModules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting wherePosCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPassphrase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereShowProductsDetailsInPurchaseTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereShowProductsDetailsInSalesTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSiteLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSiteLogoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSiteTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereStaffAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereStorageProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSubscriptionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereVatRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereWithoutStock($value)
 * @mixin \Eloquent
 */
class GeneralSetting extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
            'google_login_enabled' => 'boolean',
            'facebook_login_enabled' => 'boolean',
            'github_login_enabled' => 'boolean',
            'aws_use_path_style_endpoint' => 'boolean',
            'ftp_passive' => 'boolean',
            'ftp_ssl' => 'boolean',
        ];
    }
}
