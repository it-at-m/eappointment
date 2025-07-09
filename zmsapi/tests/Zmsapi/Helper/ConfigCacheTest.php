<?php

namespace BO\Zmsapi\Tests\Helper;

use BO\Zmsapi\Helper\ConfigCache;
use BO\Zmsapi\Tests\Base;
use BO\Zmsentities\Config as ConfigEntity;

class ConfigCacheTest extends Base
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock cache if not available
        if (!isset(\App::$cache)) {
            $this->markTestSkipped('Cache not available for testing');
        }
    }

    public function testGetConfigWithCache()
    {
        // Clear cache first
        ConfigCache::clearCache();
        
        // First call should miss cache and load from database
        $config1 = ConfigCache::getConfig();
        $this->assertInstanceOf(ConfigEntity::class, $config1);
        
        // Second call should hit cache
        $config2 = ConfigCache::getConfig();
        $this->assertInstanceOf(ConfigEntity::class, $config2);
        $this->assertEquals($config1, $config2);
    }

    public function testClearCache()
    {
        // Load config to populate cache
        ConfigCache::getConfig();
        
        // Clear cache
        ConfigCache::clearCache();
        
        // Next call should miss cache again
        $config = ConfigCache::getConfig();
        $this->assertInstanceOf(ConfigEntity::class, $config);
    }

    public function testCacheWithoutCacheAvailable()
    {
        // Temporarily remove cache
        $originalCache = \App::$cache;
        unset(\App::$cache);
        
        // Should still work without cache
        $config = ConfigCache::getConfig();
        $this->assertInstanceOf(ConfigEntity::class, $config);
        
        // Restore cache
        \App::$cache = $originalCache;
    }
} 