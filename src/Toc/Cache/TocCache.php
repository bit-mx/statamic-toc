<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc\Cache;

use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

final class TocCache
{
    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        $cache = $this->resolveStore();

        return $cache->remember($key, $ttl, $callback);
    }

    public function isEnabled(): bool
    {
        return (bool) config('statamic-toc.cache.enabled', false);
    }

    public function buildKey(string $source, mixed $content, int $minLevel, int $maxLevel, bool $tree): string
    {
        $prefix = (string) config('statamic-toc.cache.prefix', 'statamic_toc');
        $fingerprint = sha1(json_encode([
            'source' => $source,
            'content' => $content,
            'min_level' => $minLevel,
            'max_level' => $maxLevel,
            'tree' => $tree,
        ], JSON_THROW_ON_ERROR));

        return sprintf('%s:%s', $prefix, $fingerprint);
    }

    public function ttl(): int
    {
        return max(1, (int) config('statamic-toc.cache.ttl', 600));
    }

    private function resolveStore(): CacheRepository
    {
        $store = config('statamic-toc.cache.store');

        if (is_string($store) && $store !== '') {
            return Cache::store($store);
        }

        return Cache::store();
    }
}
