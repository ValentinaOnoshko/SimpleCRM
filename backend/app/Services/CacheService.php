<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function get(string $key, callable $callback, int $ttl = 60)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function clear(string $prefix): void
    {
        Cache::forget($prefix);
        Cache::flush();
    }

    public function generateCacheKey(string $prefix, array $params): string
    {
        $hash = md5(json_encode($params));
        return "{$prefix}_{$hash}";
    }
}
