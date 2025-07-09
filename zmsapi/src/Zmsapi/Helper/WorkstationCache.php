<?php

namespace BO\Zmsapi\Helper;

use BO\Zmsentities\Workstation;
use BO\Zmsdb\Workstation as WorkstationQuery;

/**
 * Helper class for caching workstation data
 */
class WorkstationCache
{
    private const CACHE_KEY_PREFIX = 'workstation_';

    /**
     * Get cached workstation data or fetch from database
     */
    public static function getWorkstation(string $loginName, int $resolveReferences = 0): ?Workstation
    {
        $cacheKey = self::generateCacheKey($loginName, $resolveReferences);

        // Try to get from cache first
        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            // Log cache hit
            if (isset(\App::$log)) {
                \App::$log->info('Workstation cache hit', [
                    'key' => $cacheKey,
                    'user_id' => $loginName,
                    'resolve_references' => $resolveReferences,
                    'workstation_id' => $cachedData->id ?? 'unknown'
                ]);
            }
            return $cachedData;
        }

        // Cache miss - fetch from database
        $workstation = (new WorkstationQuery())->readEntity($loginName, $resolveReferences);

        if (!$workstation || !$workstation->hasId()) {
            return null;
        }

        // Cache the result
        self::setCachedWorkstation($cacheKey, $workstation);

        return $workstation;
    }

    /**
     * Set workstation data in cache
     */
    private static function setCachedWorkstation(string $cacheKey, Workstation $workstation): void
    {
        if (\App::$cache) {
            \App::$cache->set($cacheKey, $workstation, \App::$PSR16_CACHE_TTL_ZMSAPI);

            // Log cache set
            if (isset(\App::$log)) {
                \App::$log->info('Workstation cache set', [
                    'key' => $cacheKey,
                    'user_id' => $workstation->useraccount['id'] ?? 'unknown',
                    'ttl' => \App::$PSR16_CACHE_TTL_ZMSAPI,
                    'workstation_id' => $workstation->id ?? 'unknown'
                ]);
            }
        }
    }

    /**
     * Generate cache key for workstation
     */
    private static function generateCacheKey(string $loginName, int $resolveReferences): string
    {
        return self::CACHE_KEY_PREFIX . md5($loginName . '_' . $resolveReferences);
    }

    /**
     * Clear cache for a specific user
     */
    public static function clearUserCache(string $loginName): void
    {
        if (!\App::$cache) {
            return;
        }

        // Clear cache for all resolve levels (0, 1, 2)
        for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
            $cacheKey = self::generateCacheKey($loginName, $resolveReferences);
            \App::$cache->delete($cacheKey);
        }

        // Log cache clear
        if (isset(\App::$log)) {
            \App::$log->info('Workstation cache cleared for user', [
                'user_id' => $loginName,
                'resolve_levels' => [0, 1, 2]
            ]);
        }
    }

    /**
     * Clear all workstation cache
     */
    public static function clearAllCache(): void
    {
        if (\App::$cache) {
            \App::$cache->clear();

            // Log cache clear
            if (isset(\App::$log)) {
                \App::$log->info('All workstation cache cleared');
            }
        }
    }
}
