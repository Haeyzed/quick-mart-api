<?php

declare(strict_types=1);

use Carbon\Carbon;

/**
 * Normalize date/time input to SQL datetime format.
 *
 * This helper function converts various date formats to a standardized
 * SQL datetime string (Y-m-d H:i:s). It handles multiple input formats
 * and separators, with fallback to current datetime if parsing fails.
 *
 * @param string|null $input The date/time input string to normalize
 * @param bool $useCurrentTime Whether to use current time if only date is provided
 * @return string Normalized datetime string in format 'Y-m-d H:i:s'
 */
if (!function_exists('normalize_to_sql_datetime')) {
    function normalize_to_sql_datetime(?string $input, bool $useCurrentTime = false): string
    {
        if (empty($input)) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        $input = trim($input);

        // Replace multiple possible separators with "-"
        $normalized = preg_replace('/[\/\.\s]+/', '-', $input);

        // Formats to test (you can add more if needed)
        $formats = [
            'd-m-Y',
            'd/m/Y',
            'd.m.Y',
            'm-d-Y',
            'm/d/Y',
            'm.d.Y',
            'Y-m-d',
            'Y/m/d',
            'Y.m.d',
        ];

        foreach ($formats as $fmt) {
            try {
                $date = Carbon::createFromFormat($fmt, $normalized);

                if ($date !== false) {
                    if ($useCurrentTime) {
                        // Inject current time if only date provided
                        $date->setTimeFrom(Carbon::now());
                    }
                    return $date->format('Y-m-d H:i:s');
                }
            } catch (Exception $e) {
                // Just continue to next format
                continue;
            }
        }

        // Fallback: try Carbon::parse (loose parsing)
        try {
            $date = Carbon::parse($input);
            if ($useCurrentTime) {
                $date->setTimeFrom(Carbon::now());
            }
            return $date->format('Y-m-d') . ' ' . date('H:i:s');
        } catch (Exception $e) {
            // Totally failed → return current datetime
            return Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}

