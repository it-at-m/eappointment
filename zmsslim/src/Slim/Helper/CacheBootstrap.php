<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

use BO\Slim\LoggerService;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Shared filesystem cache bootstrap for module Application classes.
 */
final class CacheBootstrap
{
    /**
     * @return array{0: string, 1: int}
     */
    public static function resolveConfig(?string $fallbackCacheDir = null): array
    {
        $cacheDir = getenv('CACHE_DIR') ?: ($fallbackCacheDir ?? sys_get_temp_dir());
        $ttl = (int) (getenv('SOURCE_CACHE_TTL') ?: 3600);

        return [$cacheDir, $ttl];
    }

    public static function create(string $cacheDir, int $ttl): CacheInterface
    {
        self::validateDirectory($cacheDir);

        $psr6 = new FilesystemAdapter(namespace: '', defaultLifetime: $ttl, directory: $cacheDir);
        $cache = new Psr16Cache($psr6);
        LoggerService::$cache = $cache;

        return $cache;
    }

    public static function createFromEnv(?string $fallbackCacheDir = null): CacheInterface
    {
        [$cacheDir, $ttl] = self::resolveConfig($fallbackCacheDir);

        return self::create($cacheDir, $ttl);
    }

    public static function validateDirectory(string $cacheDir): void
    {
        if (!is_dir($cacheDir)) {
            if (!@mkdir($cacheDir, 0750, true) && !is_dir($cacheDir)) {
                throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', $cacheDir));
            }
        }

        if (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" is not writable', $cacheDir));
        }
    }
}
