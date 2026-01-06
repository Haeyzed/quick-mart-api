<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Cache Forget Trait
 *
 * Provides a simple method to forget cache entries by key.
 * This trait can be used in any class that needs to clear cached data.
 *
 * @package App\Traits
 */
trait CacheForget
{
    /**
     * Forget a cache entry by its key.
     *
     * @param string $key The cache key to forget
     * @return bool True if the cache entry was successfully forgotten, false otherwise
     */
    public function cacheForget(string $key): bool
    {
        return Cache::forget($key);
    }
}

