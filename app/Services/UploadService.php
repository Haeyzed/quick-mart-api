<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\GeneralSettingsTrait;
use App\Traits\StorageProviderInfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class UploadService
 * * A robust, provider-agnostic file management service supporting dynamic 
 * disk resolution (S3, Local, DigitalOcean) based on application settings.
 */
class UploadService
{
    use GeneralSettingsTrait;
    use StorageProviderInfo;

    /**
     * Store a file and return the public URL in one call.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $disk
     * @param string|null $fileName
     * @return string
     */
    public function uploadAndGetUrl(UploadedFile $file, string $directory = 'uploads', ?string $disk = null, ?string $fileName = null): string
    {
        $resolvedDisk = $this->resolveDisk($disk);
        $filePath = $this->upload($file, $directory, $resolvedDisk, $fileName);

        return Storage::disk($resolvedDisk)->url($filePath);
    }

    /**
     * Pure file storage logic.
     * * @return string The relative file path.
     */
    public function upload(UploadedFile $file, string $directory = 'uploads', ?string $disk = null, ?string $fileName = null): string
    {
        $resolvedDisk = $this->resolveDisk($disk);
        $extension = $file->getClientOriginalExtension();

        $finalFileName = $fileName 
            ? (str_contains($fileName, '.') ? $fileName : "{$fileName}.{$extension}")
            : $this->generateFileName($extension);

        Storage::disk($resolvedDisk)->putFileAs($directory, $file, $finalFileName);

        return "{$directory}/{$finalFileName}";
    }

    /**
     * Get the public URL with disk verification.
     */
    public function url(?string $filePath, ?string $disk = null): ?string
    {
        if (!$filePath) return null;
        $resolvedDisk = $this->resolveDisk($disk);

        return Storage::disk($resolvedDisk)->exists($filePath)
            ? Storage::disk($resolvedDisk)->url($filePath)
            : null;
    }

    /**
     * Delete file from storage.
     */
    public function delete(?string $filePath, ?string $disk = null): bool
    {
        if (!$filePath) return false;
        $resolvedDisk = $this->resolveDisk($disk);

        return Storage::disk($resolvedDisk)->exists($filePath) && Storage::disk($resolvedDisk)->delete($filePath);
    }

    /**
     * Centralized disk resolution logic.
     */
    private function resolveDisk(?string $disk): string
    {
        $resolved = $disk ?? $this->getStorageProvider();
        $this->setStorageProviderInfo($resolved);
        return $resolved;
    }

    /**
     * Generate a collision-resistant filename.
     */
    protected function generateFileName(string $extension): string
    {
        return Str::uuid() . '.' . $extension;
    }
}