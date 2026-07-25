<?php

declare(strict_types=1);

namespace BO\Slim\Traits;

use BO\Slim\Helper\CacheBootstrap;

/**
 * Shared cache initialization for module Application classes.
 *
 * Expects the using class to declare:
 * - public static ?\Psr\SimpleCache\CacheInterface $cache
 * - public static string $CACHE_DIR
 * - public static int $SOURCE_CACHE_TTL
 */
trait CacheInitializationTrait
{
    private static function initializeCache(?string $fallbackCacheDir = null): void
    {
        [$cacheDir, $ttl] = CacheBootstrap::resolveConfig($fallbackCacheDir);
        static::$CACHE_DIR = $cacheDir;
        static::$SOURCE_CACHE_TTL = $ttl;
        static::$cache = CacheBootstrap::create($cacheDir, $ttl);
    }

    private static function validateCacheDirectory(): void
    {
        CacheBootstrap::validateDirectory(static::$CACHE_DIR);
    }

    private static function setupCache(): void
    {
        static::$cache = CacheBootstrap::create(static::$CACHE_DIR, static::$SOURCE_CACHE_TTL);
    }
}
