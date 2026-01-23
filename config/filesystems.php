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
        
        return $credentials;
    } catch (\Exception | \Error $e) {
        // If database is not available (e.g., during migrations or config:cache), 
        // return empty array to allow fallback to environment variables
        return [];
    }
}

$dbCredentials = getStorageCredentialsFromDatabase();

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
            'cloud_name' => $dbCredentials['cloudinary']['cloud_name'] ?? env('CLOUDINARY_CLOUD_NAME'),
            'api_key' => $dbCredentials['cloudinary']['api_key'] ?? env('CLOUDINARY_API_KEY'),
            'api_secret' => $dbCredentials['cloudinary']['api_secret'] ?? env('CLOUDINARY_API_SECRET'),
            'secure_url' => $dbCredentials['cloudinary']['secure_url'] ?? env('CLOUDINARY_SECURE_URL', true),
            'throw' => false,
            'report' => false,
        ],

        'sftp' => [
            'driver' => 'sftp',
            'host' => env('SFTP_HOST'),
            'username' => env('SFTP_USERNAME'),
            'password' => env('SFTP_PASSWORD'),
            'privateKey' => env('SFTP_PRIVATE_KEY'),
            'passphrase' => env('SFTP_PASSPHRASE'),
            'port' => env('SFTP_PORT', 22),
            'root' => env('SFTP_ROOT', '/'),
            'timeout' => 30,
            'directoryPerm' => 0755,
            'throw' => false,
            'report' => false,
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'port' => env('FTP_PORT', 21),
            'root' => env('FTP_ROOT', '/'),
            'passive' => env('FTP_PASSIVE', true),
            'ssl' => env('FTP_SSL', false),
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
