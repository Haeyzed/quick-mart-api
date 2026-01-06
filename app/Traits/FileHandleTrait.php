<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * File Handle Trait
 *
 * Provides methods for file operations such as deletion.
 * This trait is useful for managing file uploads and storage operations.
 *
 * @package App\Traits
 */
trait FileHandleTrait
{
    /**
     * Delete a file from the specified path.
     *
     * This method attempts to delete a file from either:
     * - Public storage: public/{$filePath}{$fileName}
     * - Custom path: {$filePath}{$fileName}
     *
     * @param string $filePath The directory path where the file is located
     * @param string|null $fileName The name of the file to delete
     * @return bool True if the file was deleted or didn't exist, false on error
     */
    protected function fileDelete(string $filePath, ?string $fileName): bool
    {
        if (empty($fileName)) {
            return false;
        }

        $publicPath = public_path($filePath . $fileName);
        $customPath = $filePath . $fileName;

        // Try to delete from public path first
        if (File::exists($publicPath)) {
            return File::delete($publicPath);
        }

        // Try to delete from custom path
        if (File::exists($customPath)) {
            return File::delete($customPath);
        }

        // File doesn't exist, consider it successful
        return true;
    }

    /**
     * Delete a file using Laravel Storage facade.
     *
     * @param string $disk The storage disk name (e.g., 'public', 'local')
     * @param string $filePath The full path to the file within the disk
     * @return bool True if the file was deleted or didn't exist, false on error
     */
    protected function fileDeleteFromStorage(string $disk, string $filePath): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return true;
    }
}

