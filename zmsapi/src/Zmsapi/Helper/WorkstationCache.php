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
        // Fetch the workstation data (without using cache)
        $workstation = (new WorkstationQuery())->readEntity($loginName, $resolveReferences);
        if (!$workstation || !$workstation->hasId()) {
            return null;
        }

        // Generate cache key as hash of the serialized workstation response
        $serialized = json_encode($workstation);
        $cacheKey = self::CACHE_KEY_PREFIX . md5($serialized);

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
