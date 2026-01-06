<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * ENV File Put Content Trait
 *
 * Provides methods for updating environment file (.env) values programmatically.
 * This trait should be used with caution as it modifies the environment file directly.
 *
 * @package App\Traits
 */
trait ENVFilePutContent
{
    /**
     * Write or update a key-value pair in the .env file.
     *
     * This method finds the existing key in the .env file and replaces its value.
     * If the key doesn't exist, it will not be added (only updates existing keys).
     *
     * @param string $key The environment key to update
     * @param string $value The new value to set
     * @return bool True if the update was successful, false otherwise
     */
    public function dataWriteInENVFile(string $key, string $value): bool
    {
        try {
            $path = app()->environmentFilePath();

            if (!File::exists($path)) {
                Log::error('ENVFilePutContent: Environment file not found', ['path' => $path]);
                return false;
            }

            $currentValue = env($key, '');
            $fileContent = File::get($path);

            // Escape special regex characters in key and value
            $escapedKey = preg_quote($key, '/');
            $escapedCurrentValue = preg_quote($currentValue, '/');

            // Pattern to match: KEY=value or KEY="value" or KEY='value'
            $pattern = "/^{$escapedKey}=(.*)$/m";
            $replacement = "{$key}={$value}";

            $newContent = preg_replace($pattern, $replacement, $fileContent);

            if ($newContent === null) {
                Log::error('ENVFilePutContent: Failed to replace environment variable', [
                    'key' => $key,
                ]);
                return false;
            }

            // Only write if content changed
            if ($newContent !== $fileContent) {
                File::put($path, $newContent);

                // Clear config cache to reflect changes
                if (function_exists('artisan')) {
                    Artisan::call('config:clear');
                }

                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('ENVFilePutContent: Exception while updating environment file', [
                'key' => $key,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

