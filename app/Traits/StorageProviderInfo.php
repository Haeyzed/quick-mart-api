<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\GeneralSetting;

/**
 * Storage Provider Info Trait
 *
 * Provides methods to configure storage provider settings dynamically.
 * This trait allows runtime configuration of storage settings from the database.
 *
 * @package App\Traits
 */
trait StorageProviderInfo
{
    /**
     * Set storage provider configuration from GeneralSetting model.
     *
     * This method automatically detects the storage provider and sets the appropriate configuration.
     *
     * @param string $provider Storage provider name (cloudinary, s3, ftp, sftp)
     * @param GeneralSetting|null $generalSetting The general setting model instance (optional, will fetch if not provided)
     * @return void
     */
    public function setStorageProviderInfo(string $provider, ?GeneralSetting $generalSetting = null): void
    {
        if (!$generalSetting) {
            $generalSetting = GeneralSetting::latest()->first();
        }

        if (!$generalSetting) {
            return;
        }

        match ($provider) {
            'cloudinary' => $this->setCloudinaryInfo($generalSetting),
            's3' => $this->setS3Info($generalSetting),
            'ftp' => $this->setFtpInfo($generalSetting),
            'sftp' => $this->setSftpInfo($generalSetting),
            default => null,
        };
    }

    /**
     * Set Cloudinary configuration from GeneralSetting model.
     *
     * @param GeneralSetting $generalSetting The general setting model instance
     * @return void
     */
    protected function setCloudinaryInfo(GeneralSetting $generalSetting): void
    {
        $cloudName = $generalSetting->cloudinary_cloud_name ?? env('CLOUDINARY_CLOUD_NAME');
        $apiKey = $generalSetting->cloudinary_api_key ?? env('CLOUDINARY_API_KEY') ?? env('CLOUDINARY_KEY');
        $apiSecret = $generalSetting->cloudinary_api_secret ?? env('CLOUDINARY_API_SECRET') ?? env('CLOUDINARY_SECRET');
        $secure = $generalSetting->cloudinary_secure_url ?? env('CLOUDINARY_SECURE_URL', true);

        // Build Cloudinary URL if we have all required credentials
        // Format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME
        $url = env('CLOUDINARY_URL');
        if (empty($url) && !empty($cloudName) && !empty($apiKey) && !empty($apiSecret)) {
            $url = "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}";
        }

        // Set filesystem disk configuration
        if (!empty($url)) {
            config()->set('filesystems.disks.cloudinary.url', $url);
        }

        config()->set('filesystems.disks.cloudinary.cloud', $cloudName ?: null);
        config()->set('filesystems.disks.cloudinary.key', $apiKey ?: null);
        config()->set('filesystems.disks.cloudinary.secret', $apiSecret ?: null);
        config()->set('filesystems.disks.cloudinary.secure', $secure);
        config()->set('filesystems.disks.cloudinary.cloud_name', $cloudName ?: null);
        config()->set('filesystems.disks.cloudinary.api_key', $apiKey ?: null);
        config()->set('filesystems.disks.cloudinary.api_secret', $apiSecret ?: null);
        config()->set('filesystems.disks.cloudinary.secure_url', $secure);

        // Set cloudinary config
        config()->set('cloudinary.cloud_url', $url);
    }

    /**
     * Set S3 configuration from GeneralSetting model.
     *
     * @param GeneralSetting $generalSetting The general setting model instance
     * @return void
     */
    protected function setS3Info(GeneralSetting $generalSetting): void
    {
        config()->set('filesystems.disks.s3.key', $generalSetting->aws_access_key_id ?? env('AWS_ACCESS_KEY_ID'));
        config()->set('filesystems.disks.s3.secret', $generalSetting->aws_secret_access_key ?? env('AWS_SECRET_ACCESS_KEY'));
        config()->set('filesystems.disks.s3.region', $generalSetting->aws_default_region ?? env('AWS_DEFAULT_REGION'));
        config()->set('filesystems.disks.s3.bucket', $generalSetting->aws_bucket ?? env('AWS_BUCKET'));
        config()->set('filesystems.disks.s3.url', $generalSetting->aws_url ?? env('AWS_URL'));
        config()->set('filesystems.disks.s3.endpoint', $generalSetting->aws_endpoint ?? env('AWS_ENDPOINT'));
        config()->set('filesystems.disks.s3.use_path_style_endpoint', $generalSetting->aws_use_path_style_endpoint ?? env('AWS_USE_PATH_STYLE_ENDPOINT', false));
    }

    /**
     * Set FTP configuration from GeneralSetting model.
     *
     * @param GeneralSetting $generalSetting The general setting model instance
     * @return void
     */
    protected function setFtpInfo(GeneralSetting $generalSetting): void
    {
        config()->set('filesystems.disks.ftp.host', $generalSetting->ftp_host ?? env('FTP_HOST'));
        config()->set('filesystems.disks.ftp.username', $generalSetting->ftp_username ?? env('FTP_USERNAME'));
        config()->set('filesystems.disks.ftp.password', $generalSetting->ftp_password ?? env('FTP_PASSWORD'));
        config()->set('filesystems.disks.ftp.port', $generalSetting->ftp_port ?? env('FTP_PORT', 21));
        config()->set('filesystems.disks.ftp.root', $generalSetting->ftp_root ?? env('FTP_ROOT', '/'));
        config()->set('filesystems.disks.ftp.passive', $generalSetting->ftp_passive ?? env('FTP_PASSIVE', true));
        config()->set('filesystems.disks.ftp.ssl', $generalSetting->ftp_ssl ?? env('FTP_SSL', false));
    }

    /**
     * Set SFTP configuration from GeneralSetting model.
     *
     * @param GeneralSetting $generalSetting The general setting model instance
     * @return void
     */
    protected function setSftpInfo(GeneralSetting $generalSetting): void
    {
        config()->set('filesystems.disks.sftp.host', $generalSetting->sftp_host ?? env('SFTP_HOST'));
        config()->set('filesystems.disks.sftp.username', $generalSetting->sftp_username ?? env('SFTP_USERNAME'));
        config()->set('filesystems.disks.sftp.password', $generalSetting->sftp_password ?? env('SFTP_PASSWORD'));
        config()->set('filesystems.disks.sftp.privateKey', $generalSetting->sftp_private_key ?? env('SFTP_PRIVATE_KEY'));
        config()->set('filesystems.disks.sftp.passphrase', $generalSetting->sftp_passphrase ?? env('SFTP_PASSPHRASE'));
        config()->set('filesystems.disks.sftp.port', $generalSetting->sftp_port ?? env('SFTP_PORT', 22));
        config()->set('filesystems.disks.sftp.root', $generalSetting->sftp_root ?? env('SFTP_ROOT', '/'));
    }
}
