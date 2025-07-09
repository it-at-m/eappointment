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
        // First, get the user account to extract rights for cache key
        $useraccount = null;
        if (class_exists('\BO\Zmsdb\Useraccount')) {
            $useraccount = (new \BO\Zmsdb\Useraccount())->readEntity($loginName, 0);
        }

        $cacheKey = self::generateCacheKey($loginName, $resolveReferences, $useraccount);

        // Try to get from cache first
        if (class_exists('\App') && property_exists('\App', 'cache') && \App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            // Log cache hit
            if (isset(\App::$log)) {
                \App::$log->info('Workstation cache hit', [
                    'key' => $cacheKey,
                    'user_id' => $loginName,
                    'resolve_references' => $resolveReferences
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
        // Check if cache exists and is accessible
        if (!class_exists('\App') || !property_exists('\App', 'cache') || !\App::$cache) {
            return;
        }

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

    /**
     * Generate cache key for workstation
     */
    private static function generateCacheKey(string $loginName, int $resolveReferences, $useraccount = null): string
    {
        $rightsHash = '';
        if ($useraccount && isset($useraccount->rights)) {
            // Create a hash of the user rights to include in cache key
            $rightsHash = '_' . md5(serialize($useraccount->rights));
        }

        return self::CACHE_KEY_PREFIX . md5($loginName . '_' . $resolveReferences . $rightsHash);
    }

    /**
     * Clear cache for a specific user
     */
    public static function clearUserCache(string $loginName): void
    {
        // Check if cache exists and is accessible
        if (!class_exists('\App') || !property_exists('\App', 'cache') || !\App::$cache) {
            return;
        }

        // Get current user account to clear cache for current rights
        $useraccount = null;
        if (class_exists('\BO\Zmsdb\Useraccount')) {
            $useraccount = (new \BO\Zmsdb\Useraccount())->readEntity($loginName, 0);
        }

        // Clear cache for all resolve levels (0, 1, 2, 3, 4) with current rights
        for ($resolveReferences = 0; $resolveReferences <= 4; $resolveReferences++) {
            $cacheKey = self::generateCacheKey($loginName, $resolveReferences, $useraccount);
            \App::$cache->delete($cacheKey);
        }

        // Log cache clear
        if (isset(\App::$log)) {
            \App::$log->info('Workstation cache cleared for user', [
                'user_id' => $loginName,
                'resolve_levels' => [0, 1, 2, 3, 4],
                'rights_included' => $useraccount ? 'yes' : 'no'
            ]);
        }
    }

    /**
     * Clear all workstation cache
     */
    public static function clearAllCache(): void
    {
        // Check if cache exists and is accessible
        if (!class_exists('\App') || !property_exists('\App', 'cache') || !\App::$cache) {
            return;
        }

        \App::$cache->clear();

        // Log cache clear
        if (isset(\App::$log)) {
            \App::$log->info('All workstation cache cleared');
        }
    }
}
