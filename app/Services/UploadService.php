<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * UploadService
 *
 * A general-purpose file upload service that works with all Laravel storage providers.
 * Supports images, videos, documents, and any other file type.
 * Automatically converts uploaded images to PNG format using Intervention Image 3.
 */
class UploadService
{
    /**
     * Image manager instance.
     *
     * @var ImageManager
     */
    protected ImageManager $imageManager;

    /**
     * Create a new UploadService instance.
     */
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

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
     * If the file is an image, it will be converted to PNG format.
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
        // Check if the file is an image
        if ($this->isImage($file)) {
            return $this->uploadImage($file, $directory, $disk, $fileName);
        }

        // Handle non-image files
        $extension = $file->getClientOriginalExtension();
        $fileName = $fileName ?? $this->generateFileName($extension);
        $filePath = $directory . '/' . $fileName;

        Storage::disk($disk)->putFileAs(
            $directory,
            $file,
            $fileName
        );

        return $filePath;
    }

    /**
     * Check if the uploaded file is an image.
     *
     * @param UploadedFile $file The file to check
     * @return bool True if the file is an image, false otherwise
     */
    protected function isImage(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Upload an image and convert it to PNG format.
     *
     * @param UploadedFile $file The image file to upload
     * @param string $directory Directory path where the file should be stored
     * @param string|null $disk Storage disk name (default: 'public')
     * @param string|null $fileName Custom file name (without extension). If null, generates unique name
     * @return string The stored file path
     */
    protected function uploadImage(
        UploadedFile $file,
        string       $directory = 'uploads',
        ?string      $disk = 'public',
        ?string      $fileName = null
    ): string
    {
        // Generate filename
        $fileName = $fileName ?? Str::uuid()->toString();
        $filePath = $directory . '/' . $fileName;

        // Read image from uploaded file
        $image = $this->imageManager->read($file->getRealPath());

        // Convert to PNG and save directly to storage path
        $storagePath = Storage::disk($disk)->path($filePath);
        $image->toPng()->save($storagePath);

        return $filePath;
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

