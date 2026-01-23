<?php

use App\Models\GeneralSetting;

/**
 * Get storage credentials from database (GeneralSettings)
 * Falls back to environment variables if database is not available or settings not found
 * 
 * Note: This function is called during config loading, so we need to handle cases
 * where the database might not be available (e.g., during migrations)
 */
function getStorageCredentialsFromDatabase(): array
{
    try {
        $settings = GeneralSetting::latest()->first();
        
        if (!$settings) {
            return [];
        }
        
        $credentials = [];
        
        // S3 credentials - include if any S3 setting exists
        if ($settings->aws_access_key_id || $settings->aws_bucket) {
            $credentials['s3'] = [
                'key' => $settings->aws_access_key_id ?? env('AWS_ACCESS_KEY_ID'),
                'secret' => $settings->aws_secret_access_key ?? env('AWS_SECRET_ACCESS_KEY'),
                'region' => $settings->aws_default_region ?? env('AWS_DEFAULT_REGION'),
                'bucket' => $settings->aws_bucket ?? env('AWS_BUCKET'),
                'url' => $settings->aws_url ?? env('AWS_URL'),
                'endpoint' => $settings->aws_endpoint ?? env('AWS_ENDPOINT'),
                'use_path_style_endpoint' => $settings->aws_use_path_style_endpoint ?? env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            ];
        }
        
        // Cloudinary credentials - include if any Cloudinary setting exists
        if ($settings->cloudinary_cloud_name || $settings->cloudinary_api_key) {
            $credentials['cloudinary'] = [
                'cloud_name' => $settings->cloudinary_cloud_name ?? env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => $settings->cloudinary_api_key ?? env('CLOUDINARY_API_KEY'),
                'api_secret' => $settings->cloudinary_api_secret ?? env('CLOUDINARY_API_SECRET'),
                'secure_url' => $settings->cloudinary_secure_url ?? env('CLOUDINARY_SECURE_URL', true),
            ];
        }
        
        // SFTP credentials - include if any SFTP setting exists
        if ($settings->sftp_host || $settings->sftp_username) {
            $credentials['sftp'] = [
                'host' => $settings->sftp_host ?? env('SFTP_HOST'),
                'username' => $settings->sftp_username ?? env('SFTP_USERNAME'),
                'password' => $settings->sftp_password ?? env('SFTP_PASSWORD'),
                'privateKey' => $settings->sftp_private_key ?? env('SFTP_PRIVATE_KEY'),
                'passphrase' => $settings->sftp_passphrase ?? env('SFTP_PASSPHRASE'),
                'port' => $settings->sftp_port ?? env('SFTP_PORT', 22),
                'root' => $settings->sftp_root ?? env('SFTP_ROOT', '/'),
            ];
        }
        
        // FTP credentials - include if any FTP setting exists
        if ($settings->ftp_host || $settings->ftp_username) {
            $credentials['ftp'] = [
                'host' => $settings->ftp_host ?? env('FTP_HOST'),
                'username' => $settings->ftp_username ?? env('FTP_USERNAME'),
                'password' => $settings->ftp_password ?? env('FTP_PASSWORD'),
                'port' => $settings->ftp_port ?? env('FTP_PORT', 21),
                'root' => $settings->ftp_root ?? env('FTP_ROOT', '/'),
                'passive' => $settings->ftp_passive ?? env('FTP_PASSIVE', true),
                'ssl' => $settings->ftp_ssl ?? env('FTP_SSL', false),
            ];
        }
        
        return $credentials;
    } catch (\Exception | \Error $e) {
        return [];
    }
}

$dbCredentials = getStorageCredentialsFromDatabase();

// Build Cloudinary URL from environment variables if not set
// Format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME
$cloudinaryUrl = env('CLOUDINARY_URL');
if (empty($cloudinaryUrl)) {
    $cloudName = env('CLOUDINARY_CLOUD_NAME');
    $apiKey = env('CLOUDINARY_API_KEY') ?? env('CLOUDINARY_KEY');
    $apiSecret = env('CLOUDINARY_API_SECRET') ?? env('CLOUDINARY_SECRET');
    
    if (!empty($cloudName) && !empty($apiKey) && !empty($apiSecret)) {
        $cloudinaryUrl = "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}";
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => $dbCredentials['s3']['key'] ?? env('AWS_ACCESS_KEY_ID'),
            'secret' => $dbCredentials['s3']['secret'] ?? env('AWS_SECRET_ACCESS_KEY'),
            'region' => $dbCredentials['s3']['region'] ?? env('AWS_DEFAULT_REGION'),
            'bucket' => $dbCredentials['s3']['bucket'] ?? env('AWS_BUCKET'),
            'url' => $dbCredentials['s3']['url'] ?? env('AWS_URL'),
            'endpoint' => $dbCredentials['s3']['endpoint'] ?? env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => $dbCredentials['s3']['use_path_style_endpoint'] ?? env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'cloudinary' => [
            'driver' => 'cloudinary',
            'url' => $cloudinaryUrl,
            'cloud' => env('CLOUDINARY_CLOUD_NAME'),
            'key' => env('CLOUDINARY_API_KEY') ?? env('CLOUDINARY_KEY'),
            'secret' => env('CLOUDINARY_API_SECRET') ?? env('CLOUDINARY_SECRET'),
            'secure' => env('CLOUDINARY_SECURE_URL', true),
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key' => env('CLOUDINARY_API_KEY') ?? env('CLOUDINARY_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET') ?? env('CLOUDINARY_SECRET'),
            'secure_url' => env('CLOUDINARY_SECURE_URL', true),
            'throw' => false,
            'report' => false,
        ],

        'sftp' => [
            'driver' => 'sftp',
            'host' => $dbCredentials['sftp']['host'] ?? env('SFTP_HOST'),
            'username' => $dbCredentials['sftp']['username'] ?? env('SFTP_USERNAME'),
            'password' => $dbCredentials['sftp']['password'] ?? env('SFTP_PASSWORD'),
            'privateKey' => $dbCredentials['sftp']['privateKey'] ?? env('SFTP_PRIVATE_KEY'),
            'passphrase' => $dbCredentials['sftp']['passphrase'] ?? env('SFTP_PASSPHRASE'),
            'port' => $dbCredentials['sftp']['port'] ?? env('SFTP_PORT', 22),
            'root' => $dbCredentials['sftp']['root'] ?? env('SFTP_ROOT', '/'),
            'timeout' => 30,
            'directoryPerm' => 0755,
            'throw' => false,
            'report' => false,
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => $dbCredentials['ftp']['host'] ?? env('FTP_HOST'),
            'username' => $dbCredentials['ftp']['username'] ?? env('FTP_USERNAME'),
            'password' => $dbCredentials['ftp']['password'] ?? env('FTP_PASSWORD'),
            'port' => $dbCredentials['ftp']['port'] ?? env('FTP_PORT', 21),
            'root' => $dbCredentials['ftp']['root'] ?? env('FTP_ROOT', '/'),
            'passive' => $dbCredentials['ftp']['passive'] ?? env('FTP_PASSIVE', true),
            'ssl' => $dbCredentials['ftp']['ssl'] ?? env('FTP_SSL', false),
            'timeout' => 30,
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];