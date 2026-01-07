<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * UploadService
 *
 * A general-purpose file upload service that works with all Laravel storage providers.
 * Supports images, videos, documents, and any other file type.
 */
class UploadService
{

    /**
     * Upload a file and return its public URL.
     *
     * @param UploadedFile $file The file to upload
     * @param string $directory Directory path where the file should be stored
     * @param string|null $disk Storage disk name (default: 'public')
     * @param string|null $fileName Custom file name (without extension). If null, generates unique name
     * @return string The public URL of the uploaded file
     */
    public function uploadAndGetUrl(
        UploadedFile $file,
        string       $directory = 'uploads',
        ?string      $disk = 'public',
        ?string      $fileName = null
    ): string
    {
        $filePath = $this->upload($file, $directory, $disk, $fileName);

        return Storage::disk($disk)->url($filePath);
    }

    /**
     * Upload a file to storage.
     *
     * @param UploadedFile $file The file to upload
     * @param string $directory Directory path where the file should be stored
     * @param string|null $disk Storage disk name (default: 'public')
     * @param string|null $fileName Custom file name (without extension). If null, generates unique name
     * @return string The stored file path
     */
    public function upload(
        UploadedFile $file,
        string       $directory = 'uploads',
        ?string      $disk = 'public',
        ?string      $fileName = null
    ): string
    {
        // Generate filename with original extension if not provided
        if ($fileName === null) {
            $extension = $file->getClientOriginalExtension();
            $fileName = $this->generateFileName($extension);
        } else {
            // If fileName is provided without extension, add original extension
            $extension = $file->getClientOriginalExtension();
            if (!str_contains($fileName, '.')) {
                $fileName .= '.' . $extension;
            }
        }

        // Ensure directory exists
        Storage::disk($disk)->makeDirectory($directory);

        // Upload file
        Storage::disk($disk)->putFileAs(
            $directory,
            $file,
            $fileName
        );

        return $directory . '/' . $fileName;
    }


    /**
     * Generate a unique file name.
     *
     * @param string $extension File extension
     * @return string Unique file name with extension
     */
    protected function generateFileName(string $extension): string
    {
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Get the public URL of a file.
     *
     * @param string $filePath Path to the file
     * @param string|null $disk Storage disk name (default: 'public')
     * @return string|null The public URL or null if file doesn't exist
     */
    public function url(string $filePath, ?string $disk = 'public'): ?string
    {
        if (!Storage::disk($disk)->exists($filePath)) {
            return null;
        }

        return Storage::disk($disk)->url($filePath);
    }

    /**
     * Check if a file exists in storage.
     *
     * @param string $filePath Path to the file
     * @param string|null $disk Storage disk name (default: 'public')
     * @return bool True if file exists, false otherwise
     */
    public function exists(string $filePath, ?string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($filePath);
    }

    /**
     * Delete a file from storage.
     *
     * @param string $filePath Path to the file
     * @param string|null $disk Storage disk name (default: 'public')
     * @return bool True if file was deleted, false otherwise
     */
    public function delete(string $filePath, ?string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }
}

