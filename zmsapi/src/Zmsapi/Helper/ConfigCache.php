<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi\Helper;

use BO\Zmsdb\Config as ConfigQuery;

class ConfigCache
{
    const CACHE_KEY = 'config';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get config from cache or database
     *
     * @return \BO\Zmsentities\Config
     */
    public static function getConfig()
    {
        if (!isset(\App::$cache)) {
            return (new ConfigQuery())->readEntity();
        }

        $cacheKey = self::CACHE_KEY;

        // Try to get from cache first
        $cachedConfig = \App::$cache->get($cacheKey);
        if ($cachedConfig !== null) {
            \App::$log->info('Config cache hit', ['key' => $cacheKey]);
            return $cachedConfig;
        }

        \App::$log->info('Config cache miss, loading from database', ['key' => $cacheKey]);
        $config = (new ConfigQuery())->readEntity();

        // Store in cache with TTL
        \App::$cache->set($cacheKey, $config, self::CACHE_TTL);

        \App::$log->info('Config cached', ['key' => $cacheKey, 'ttl' => self::CACHE_TTL]);

        return $config;
    }

    /**
     * Clear config cache
     */
    public static function clearCache()
    {
        if (!isset(\App::$cache)) {
            return;
        }

        $cacheKey = self::CACHE_KEY;
        \App::$cache->delete($cacheKey);
        \App::$log->info('Config cache cleared', ['key' => $cacheKey]);
    }
}
