<?php

namespace BO\Zmsapi\Helper;

use BO\Zmsentities\Workstation;
use BO\Zmsdb\Workstation as WorkstationQuery;

/**
 * Helper class for caching workstation data
 *
 * Caching Strategy:
 * - Cache key is generated from workstation data excluding dynamic timestamp fields
 * - This prevents cache misses due to frequently changing timestamps while still
 *   ensuring cache invalidation when actual data changes
 * - Excluded fields from cache key generation:
 *   - useraccount.lastLogin: changes on every login
 *   - scope.lastChange: changes when scope data is modified
 *   - scope.status.queue.lastGivenNumberTimestamp: changes when new queue numbers are given
 * - The complete workstation data (including timestamps) is still cached and returned
 */
class WorkstationCache
{
    private const CACHE_KEY_PREFIX = 'workstation_';

    /**
     * Get cached workstation data or fetch from database
     */
    public static function getWorkstation(string $loginName, int $resolveReferences = 0): ?Workstation
    {
        // Fetch the workstation data (without using cache)
        $workstation = (new WorkstationQuery())->readEntity($loginName, $resolveReferences);
        if (!$workstation || !$workstation->hasId()) {
            return null;
        }

        // Generate cache key based on workstation data excluding lastLogin field
        // This prevents cache misses due to changing lastLogin timestamps
        $workstationData = self::getWorkstationDataForCacheKey($workstation);
        $cacheKey = self::CACHE_KEY_PREFIX . md5($workstationData);

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

        // Cache the result
        self::setCachedWorkstation($cacheKey, $workstation);

        return $workstation;
    }

    /**
     * Get workstation data for cache key generation, excluding dynamic fields
     */
    private static function getWorkstationDataForCacheKey(Workstation $workstation): string
    {
        // Convert workstation to array
        $workstationArray = $workstation->getArrayCopy();

        // Remove lastLogin from useraccount to prevent cache misses due to timestamp changes
        if (isset($workstationArray['useraccount']['lastLogin'])) {
            unset($workstationArray['useraccount']['lastLogin']);
        }

        // Remove lastChange from scope to prevent cache misses due to timestamp changes
        if (isset($workstationArray['scope']['lastChange'])) {
            unset($workstationArray['scope']['lastChange']);
        }

        // Remove lastGivenNumberTimestamp from scope status to prevent cache misses due to timestamp changes
        if (isset($workstationArray['scope']['status']['queue']['lastGivenNumberTimestamp'])) {
            unset($workstationArray['scope']['status']['queue']['lastGivenNumberTimestamp']);
        }

        return json_encode($workstationArray);
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
}
