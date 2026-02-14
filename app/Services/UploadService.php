<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\GeneralSettingsTrait;
use App\Traits\StorageProviderInfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Class UploadService
 *
 * A robust, provider-agnostic file management service.
 * Handles dynamic disk resolution (S3, Local, DigitalOcean, etc.)
 * based on application settings.
 */
class UploadService
{
    use GeneralSettingsTrait;
    use StorageProviderInfo;

    /**
     * Store a file and return the public URL in one call.
     *
     * @param UploadedFile $file The file to upload.
     * @param string $directory Target directory.
     * @param string|null $disk Specific disk (optional).
     * @param string|null $fileName Custom filename (optional).
     * @return string The public URL of the uploaded file.
     * @throws RuntimeException If upload fails.
     */
    public function uploadAndGetUrl(
        UploadedFile $file,
        string       $directory = 'uploads',
        ?string      $disk = null,
        ?string      $fileName = null
    ): string
    {
        $resolvedDisk = $this->resolveDisk($disk);
        $filePath = $this->upload($file, $directory, $resolvedDisk, $fileName);

        $url = $this->url($filePath, $resolvedDisk);

        if ($url === null) {
            // Fallback or empty string if URL generation fails (e.g. private bucket)
            return '';
        }

        return $url;
    }

    /**
     * Centralized disk resolution logic.
     *
     * @param string|null $disk
     * @return string The resolved disk name.
     */
    private function resolveDisk(?string $disk): string
    {
        // 1. Use requested disk OR fallback to system default provider
        $resolved = $disk ?: $this->getStorageProvider();

        // 2. Configure the dynamic config for this provider (from Trait)
        $this->setStorageProviderInfo($resolved);

        return $resolved;
    }

    /**
     * Pure file storage logic.
     *
     * @param UploadedFile $file The file to upload.
     * @param string $directory Target directory.
     * @param string|null $disk Specific disk (optional).
     * @param string|null $fileName Custom filename (optional).
     * @return string The relative file path.
     * @throws RuntimeException If storage write fails.
     */
    public function upload(
        UploadedFile $file,
        string       $directory = 'uploads',
        ?string      $disk = null,
        ?string      $fileName = null
    ): string
    {
        $resolvedDisk = $this->resolveDisk($disk);
        $extension = $file->getClientOriginalExtension();

        $finalFileName = $fileName
            ? (str_contains($fileName, '.') ? $fileName : "{$fileName}.{$extension}")
            : $this->generateFileName($extension);

        $path = Storage::disk($resolvedDisk)->putFileAs($directory, $file, $finalFileName);

        if ($path === false) {
            throw new RuntimeException("Failed to upload file to disk: {$resolvedDisk}");
        }

        return $path;
    }

    /**
     * Generate a collision-resistant filename.
     *
     * @param string $extension
     * @return string
     */
    protected function generateFileName(string $extension): string
    {
        return Str::uuid()->toString() . '.' . $extension;
    }

    /**
     * Get the public URL with disk verification.
     *
     * @param string|null $filePath The relative file path.
     * @param string|null $disk Specific disk (optional).
     * @return string|null The full URL or null if file is missing/path is empty.
     */
    public function url(?string $filePath, ?string $disk = null): ?string
    {
        if (empty($filePath)) {
            return null;
        }

        $resolvedDisk = $this->resolveDisk($disk);

        // Optimization: For Cloud disks, 'exists' checks can be slow.
        // We rely on the driver to generate the URL.
        return Storage::disk($resolvedDisk)->url($filePath);
    }

    /**
     * Delete file from storage.
     *
     * @param string|null $filePath The relative file path.
     * @param string|null $disk Specific disk (optional).
     * @return bool True if deleted or file didn't exist.
     */
    public function delete(?string $filePath, ?string $disk = null): bool
    {
        if (empty($filePath)) {
            return false;
        }

        $resolvedDisk = $this->resolveDisk($disk);

        if (Storage::disk($resolvedDisk)->exists($filePath)) {
            return Storage::disk($resolvedDisk)->delete($filePath);
        }

        return false;
    }
}
